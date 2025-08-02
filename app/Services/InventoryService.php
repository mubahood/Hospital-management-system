<?php

namespace App\Services;

use App\Models\StockItem;
use App\Models\StockOutRecord;
use App\Models\StockItemCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * InventoryService - Handles complex inventory management business logic
 * 
 * This service manages:
 * - Stock item management and tracking
 * - Inventory movements and adjustments
 * - Low stock alerts and reorder management
 * - Stock valuation and reporting
 */
class InventoryService
{
    /**
     * Create new stock item with validation
     */
    public function createStockItem(array $data): StockItem
    {
        return DB::transaction(function () use ($data) {
            // Generate stock number if not provided
            if (empty($data['stock_number'])) {
                $data['stock_number'] = $this->generateStockNumber();
            }

            // Set default values
            $data['current_quantity'] = $data['current_quantity'] ?? 0;
            $data['minimum_quantity'] = $data['minimum_quantity'] ?? 10;
            $data['status'] = $data['status'] ?? 'Active';

            // Calculate total value
            if (isset($data['current_quantity']) && isset($data['unit_price'])) {
                $data['total_value'] = $data['current_quantity'] * $data['unit_price'];
            }

            $stockItem = StockItem::create($data);

            Log::info('Stock item created', [
                'stock_item_id' => $stockItem->id,
                'stock_number' => $stockItem->stock_number,
                'name' => $stockItem->name
            ]);

            return $stockItem->load(['category', 'enterprise']);
        });
    }

    /**
     * Update stock item with quantity tracking
     */
    public function updateStockItem(StockItem $stockItem, array $data): StockItem
    {
        return DB::transaction(function () use ($stockItem, $data) {
            $oldQuantity = $stockItem->current_quantity;

            // Update stock item
            $stockItem->update($data);

            // Recalculate total value if quantity or price changed
            if (isset($data['current_quantity']) || isset($data['unit_price'])) {
                $quantity = $data['current_quantity'] ?? $stockItem->current_quantity;
                $price = $data['unit_price'] ?? $stockItem->unit_price;
                $stockItem->update(['total_value' => $quantity * $price]);
            }

            // Log quantity changes
            if (isset($data['current_quantity']) && $data['current_quantity'] != $oldQuantity) {
                $this->logQuantityChange($stockItem, $oldQuantity, $data['current_quantity'], 'Manual Adjustment');
            }

            Log::info('Stock item updated', [
                'stock_item_id' => $stockItem->id,
                'updated_fields' => array_keys($data)
            ]);

            return $stockItem->fresh(['category', 'enterprise']);
        });
    }

    /**
     * Process stock out (dispensing/usage)
     */
    public function processStockOut(StockItem $stockItem, array $stockOutData): StockOutRecord
    {
        return DB::transaction(function () use ($stockItem, $stockOutData) {
            $quantity = $stockOutData['quantity'];
            
            // Validate sufficient stock
            if ($quantity > $stockItem->current_quantity) {
                throw new \Exception("Insufficient stock. Available: {$stockItem->current_quantity}, Requested: {$quantity}");
            }

            // Create stock out record
            $stockOut = StockOutRecord::create([
                'stock_item_id' => $stockItem->id,
                'enterprise_id' => $stockItem->enterprise_id,
                'quantity' => $quantity,
                'unit_price' => $stockOutData['unit_price'] ?? $stockItem->unit_price,
                'total_value' => $quantity * ($stockOutData['unit_price'] ?? $stockItem->unit_price),
                'reason' => $stockOutData['reason'] ?? 'Dispensed',
                'notes' => $stockOutData['notes'] ?? null,
                'requested_by' => $stockOutData['requested_by'] ?? auth()->id(),
                'approved_by' => $stockOutData['approved_by'] ?? auth()->id(),
                'status' => 'Completed',
                'date_out' => $stockOutData['date_out'] ?? now()
            ]);

            // Update stock item quantity
            $newQuantity = $stockItem->current_quantity - $quantity;
            $stockItem->update([
                'current_quantity' => $newQuantity,
                'total_value' => $newQuantity * $stockItem->unit_price,
                'last_updated' => now()
            ]);

            // Log the transaction
            $this->logQuantityChange($stockItem, $stockItem->current_quantity + $quantity, $newQuantity, 'Stock Out');

            // Check for low stock alerts
            $this->checkLowStockAlert($stockItem);

            Log::info('Stock out processed', [
                'stock_item_id' => $stockItem->id,
                'quantity_out' => $quantity,
                'remaining_quantity' => $newQuantity
            ]);

            return $stockOut->load(['stockItem', 'requestedBy', 'approvedBy']);
        });
    }

    /**
     * Process stock in (receiving/restocking)
     */
    public function processStockIn(StockItem $stockItem, array $stockInData): StockItem
    {
        return DB::transaction(function () use ($stockItem, $stockInData) {
            $quantity = $stockInData['quantity'];
            $unitPrice = $stockInData['unit_price'] ?? $stockItem->unit_price;
            
            // Calculate new quantity and weighted average price
            $oldQuantity = $stockItem->current_quantity;
            $oldValue = $stockItem->total_value;
            $newQuantity = $oldQuantity + $quantity;
            $addedValue = $quantity * $unitPrice;
            $newTotalValue = $oldValue + $addedValue;
            $newUnitPrice = $newQuantity > 0 ? $newTotalValue / $newQuantity : $unitPrice;

            // Update stock item
            $stockItem->update([
                'current_quantity' => $newQuantity,
                'unit_price' => $newUnitPrice,
                'total_value' => $newTotalValue,
                'last_updated' => now(),
                'last_restock_date' => $stockInData['restock_date'] ?? now()
            ]);

            // Log the transaction
            $this->logQuantityChange($stockItem, $oldQuantity, $newQuantity, 'Stock In', [
                'unit_price' => $unitPrice,
                'supplier' => $stockInData['supplier'] ?? null,
                'batch_number' => $stockInData['batch_number'] ?? null
            ]);

            Log::info('Stock in processed', [
                'stock_item_id' => $stockItem->id,
                'quantity_in' => $quantity,
                'new_quantity' => $newQuantity,
                'new_unit_price' => $newUnitPrice
            ]);

            return $stockItem->fresh(['category', 'enterprise']);
        });
    }

    /**
     * Get inventory analytics for enterprise
     */
    public function getInventoryAnalytics(int $enterpriseId, array $filters = []): array
    {
        $query = StockItem::where('enterprise_id', $enterpriseId);

        // Apply category filter
        if (!empty($filters['category_id'])) {
            $query->where('stock_item_category_id', $filters['category_id']);
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $stockItems = $query->with(['category'])->get();

        return [
            'inventory_summary' => [
                'total_items' => $stockItems->count(),
                'total_value' => $stockItems->sum('total_value'),
                'active_items' => $stockItems->where('status', 'Active')->count(),
                'low_stock_items' => $stockItems->filter(function ($item) {
                    return $item->current_quantity <= $item->minimum_quantity;
                })->count(),
                'out_of_stock_items' => $stockItems->where('current_quantity', 0)->count()
            ],
            'category_breakdown' => $this->getCategoryBreakdown($stockItems),
            'low_stock_alerts' => $this->getLowStockAlerts($enterpriseId),
            'stock_movements' => $this->getRecentStockMovements($enterpriseId),
            'valuation_report' => $this->getStockValuation($stockItems),
            'reorder_suggestions' => $this->getReorderSuggestions($stockItems)
        ];
    }

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts(int $enterpriseId): array
    {
        $lowStockItems = StockItem::where('enterprise_id', $enterpriseId)
            ->whereColumn('current_quantity', '<=', 'minimum_quantity')
            ->where('status', 'Active')
            ->with(['category'])
            ->orderBy('current_quantity', 'asc')
            ->get();

        return $lowStockItems->map(function ($item) {
            $daysToStockOut = $this->calculateDaysToStockOut($item);
            
            return [
                'stock_item' => $item,
                'shortage' => $item->minimum_quantity - $item->current_quantity,
                'urgency_level' => $this->getUrgencyLevel($item),
                'days_to_stock_out' => $daysToStockOut,
                'suggested_order_quantity' => $this->calculateSuggestedOrderQuantity($item)
            ];
        })->toArray();
    }

    /**
     * Get inventory valuation report
     */
    public function getInventoryValuation(int $enterpriseId, array $filters = []): array
    {
        $query = StockItem::where('enterprise_id', $enterpriseId);

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('stock_item_category_id', $filters['category_id']);
        }

        $stockItems = $query->with(['category'])->get();

        return [
            'total_inventory_value' => $stockItems->sum('total_value'),
            'category_valuation' => $stockItems->groupBy('category.name')->map(function ($items) {
                return [
                    'items_count' => $items->count(),
                    'total_value' => $items->sum('total_value'),
                    'average_value_per_item' => $items->avg('total_value')
                ];
            }),
            'valuation_by_status' => $stockItems->groupBy('status')->map(function ($items) {
                return [
                    'items_count' => $items->count(),
                    'total_value' => $items->sum('total_value')
                ];
            }),
            'high_value_items' => $stockItems->sortByDesc('total_value')->take(10)->values(),
            'dead_stock' => $this->getDeadStock($stockItems),
            'valuation_date' => now()
        ];
    }

    /**
     * Generate stock movement report
     */
    public function getStockMovementReport(int $enterpriseId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subMonth();
        $dateTo = $filters['date_to'] ?? now();

        $stockOuts = StockOutRecord::where('enterprise_id', $enterpriseId)
            ->whereBetween('date_out', [$dateFrom, $dateTo])
            ->with(['stockItem', 'requestedBy'])
            ->get();

        return [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ],
            'movement_summary' => [
                'total_out_transactions' => $stockOuts->count(),
                'total_quantity_out' => $stockOuts->sum('quantity'),
                'total_value_out' => $stockOuts->sum('total_value')
            ],
            'movements_by_item' => $stockOuts->groupBy('stockItem.name')->map(function ($movements) {
                return [
                    'transactions' => $movements->count(),
                    'total_quantity' => $movements->sum('quantity'),
                    'total_value' => $movements->sum('total_value')
                ];
            }),
            'movements_by_reason' => $stockOuts->groupBy('reason')->map(function ($movements) {
                return [
                    'transactions' => $movements->count(),
                    'total_quantity' => $movements->sum('quantity'),
                    'total_value' => $movements->sum('total_value')
                ];
            }),
            'daily_movements' => $this->getDailyMovements($stockOuts, $dateFrom, $dateTo)
        ];
    }

    /**
     * Bulk update stock quantities
     */
    public function bulkUpdateStock(array $updates): array
    {
        $results = [];

        DB::transaction(function () use ($updates, &$results) {
            foreach ($updates as $update) {
                try {
                    $stockItem = StockItem::findOrFail($update['stock_item_id']);
                    $oldQuantity = $stockItem->current_quantity;
                    $newQuantity = $update['new_quantity'];

                    $stockItem->update([
                        'current_quantity' => $newQuantity,
                        'total_value' => $newQuantity * $stockItem->unit_price,
                        'last_updated' => now()
                    ]);

                    $this->logQuantityChange($stockItem, $oldQuantity, $newQuantity, 'Bulk Update');

                    $results[] = [
                        'stock_item_id' => $stockItem->id,
                        'status' => 'success',
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'stock_item_id' => $update['stock_item_id'] ?? null,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }
        });

        Log::info('Bulk stock update completed', [
            'total_updates' => count($updates),
            'successful' => count(array_filter($results, fn($r) => $r['status'] === 'success')),
            'failed' => count(array_filter($results, fn($r) => $r['status'] === 'error'))
        ]);

        return $results;
    }

    /**
     * Generate stock number
     */
    private function generateStockNumber(): string
    {
        $prefix = 'STK';
        $date = now()->format('Ymd');
        $lastNumber = StockItem::where('stock_number', 'like', $prefix . $date . '%')->count() + 1;

        return $prefix . $date . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Log quantity change
     */
    private function logQuantityChange(StockItem $stockItem, float $oldQuantity, float $newQuantity, string $reason, array $metadata = []): void
    {
        Log::info('Stock quantity changed', [
            'stock_item_id' => $stockItem->id,
            'stock_number' => $stockItem->stock_number,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'change_amount' => $newQuantity - $oldQuantity,
            'reason' => $reason,
            'metadata' => $metadata,
            'user_id' => auth()->id()
        ]);
    }

    /**
     * Check for low stock alert
     */
    private function checkLowStockAlert(StockItem $stockItem): void
    {
        if ($stockItem->current_quantity <= $stockItem->minimum_quantity) {
            Log::warning('Low stock alert', [
                'stock_item_id' => $stockItem->id,
                'stock_number' => $stockItem->stock_number,
                'name' => $stockItem->name,
                'current_quantity' => $stockItem->current_quantity,
                'minimum_quantity' => $stockItem->minimum_quantity
            ]);

            // Here you could trigger notifications, emails, etc.
        }
    }

    /**
     * Get category breakdown
     */
    private function getCategoryBreakdown($stockItems): array
    {
        return $stockItems->groupBy('category.name')->map(function ($items) {
            return [
                'items_count' => $items->count(),
                'total_quantity' => $items->sum('current_quantity'),
                'total_value' => $items->sum('total_value'),
                'low_stock_count' => $items->filter(function ($item) {
                    return $item->current_quantity <= $item->minimum_quantity;
                })->count()
            ];
        })->toArray();
    }

    /**
     * Get recent stock movements
     */
    private function getRecentStockMovements(int $enterpriseId, int $limit = 10): array
    {
        return StockOutRecord::where('enterprise_id', $enterpriseId)
            ->with(['stockItem', 'requestedBy'])
            ->orderBy('date_out', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get stock valuation summary
     */
    private function getStockValuation($stockItems): array
    {
        return [
            'total_value' => $stockItems->sum('total_value'),
            'average_item_value' => $stockItems->avg('total_value'),
            'highest_value_item' => $stockItems->sortByDesc('total_value')->first(),
            'lowest_value_item' => $stockItems->sortBy('total_value')->first()
        ];
    }

    /**
     * Get reorder suggestions
     */
    private function getReorderSuggestions($stockItems): array
    {
        return $stockItems->filter(function ($item) {
            return $item->current_quantity <= $item->minimum_quantity && $item->status === 'Active';
        })->map(function ($item) {
            return [
                'stock_item' => $item,
                'suggested_quantity' => $this->calculateSuggestedOrderQuantity($item),
                'urgency' => $this->getUrgencyLevel($item),
                'estimated_cost' => $this->calculateSuggestedOrderQuantity($item) * $item->unit_price
            ];
        })->values()->toArray();
    }

    /**
     * Calculate days to stock out
     */
    private function calculateDaysToStockOut(StockItem $stockItem): ?int
    {
        // This is a simplified calculation - in reality, you'd analyze usage patterns
        $avgDailyUsage = 1; // Default assumption
        
        if ($stockItem->current_quantity <= 0) {
            return 0;
        }

        return (int) ($stockItem->current_quantity / $avgDailyUsage);
    }

    /**
     * Get urgency level for stock item
     */
    private function getUrgencyLevel(StockItem $stockItem): string
    {
        if ($stockItem->current_quantity <= 0) {
            return 'Critical';
        }
        
        $ratio = $stockItem->current_quantity / $stockItem->minimum_quantity;
        
        if ($ratio <= 0.5) {
            return 'High';
        } elseif ($ratio <= 1) {
            return 'Medium';
        } else {
            return 'Low';
        }
    }

    /**
     * Calculate suggested order quantity
     */
    private function calculateSuggestedOrderQuantity(StockItem $stockItem): int
    {
        $shortage = max(0, $stockItem->minimum_quantity - $stockItem->current_quantity);
        $buffer = $stockItem->minimum_quantity * 0.5; // 50% buffer
        
        return (int) ($shortage + $buffer);
    }

    /**
     * Get dead stock items
     */
    private function getDeadStock($stockItems): array
    {
        // Items that haven't moved in 90+ days (simplified logic)
        return $stockItems->filter(function ($item) {
            return $item->last_updated && 
                   Carbon::parse($item->last_updated)->diffInDays(now()) > 90 &&
                   $item->current_quantity > 0;
        })->values()->toArray();
    }

    /**
     * Get daily movements breakdown
     */
    private function getDailyMovements($stockOuts, $dateFrom, $dateTo): array
    {
        $movements = [];
        $period = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        while ($period->lte($endDate)) {
            $dayMovements = $stockOuts->filter(function ($movement) use ($period) {
                return Carbon::parse($movement->date_out)->isSameDay($period);
            });

            $movements[] = [
                'date' => $period->format('Y-m-d'),
                'transactions' => $dayMovements->count(),
                'total_quantity' => $dayMovements->sum('quantity'),
                'total_value' => $dayMovements->sum('total_value')
            ];

            $period->addDay();
        }

        return $movements;
    }
}
