<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get items by category
     */
    public function getByCategory(string $category): Collection;

    /**
     * Get low stock items
     */
    public function getLowStockItems(): Collection;

    /**
     * Get out of stock items
     */
    public function getOutOfStockItems(): Collection;

    /**
     * Get expiring items
     */
    public function getExpiringItems(int $daysFromNow = 30): Collection;

    /**
     * Get expired items
     */
    public function getExpiredItems(): Collection;

    /**
     * Search items by name or code
     */
    public function searchItems(string $term): Collection;

    /**
     * Update item stock
     */
    public function updateStock(int $itemId, int $quantity, string $type = 'addition'): bool;

    /**
     * Get stock movements for item
     */
    public function getStockMovements(int $itemId, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection;

    /**
     * Get inventory valuation
     */
    public function getInventoryValuation(): array;

    /**
     * Get stock report
     */
    public function getStockReport(): array;

    /**
     * Create stock adjustment
     */
    public function createStockAdjustment(int $itemId, int $adjustmentQuantity, string $reason): bool;

    /**
     * Get most consumed items
     */
    public function getMostConsumedItems(int $limit = 10): Collection;

    /**
     * Get items by supplier
     */
    public function getBySupplier(int $supplierId): Collection;

    /**
     * Mark item as discontinued
     */
    public function markAsDiscontinued(int $itemId): bool;

    /**
     * Get reorder alerts
     */
    public function getReorderAlerts(): Collection;
}
