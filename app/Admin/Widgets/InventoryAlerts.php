<?php

namespace App\Admin\Widgets;

use App\Models\StockItem;
use App\Models\DoseItem;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Widget;

class InventoryAlerts extends Widget
{
    protected $view = 'admin.widgets.inventory-alerts';

    /**
     * Get inventory alerts and statistics
     */
    public function data()
    {
        $user = Admin::user();
        $enterpriseId = $user->enterprise_id;
        
        $today = Carbon::today();
        $nextMonth = Carbon::now()->addMonth();

        // Low stock items (less than 10 units)
        $lowStockItems = StockItem::where('enterprise_id', $enterpriseId)
            ->where('current_quantity', '<=', 10)
            ->where('current_quantity', '>', 0)
            ->orderBy('current_quantity', 'asc')
            ->limit(5)
            ->get(['name', 'current_quantity', 'unit']);

        // Out of stock items
        $outOfStockCount = StockItem::where('enterprise_id', $enterpriseId)
            ->where('current_quantity', '<=', 0)
            ->count();

        // Items expiring soon (within 30 days)
        $expiringSoonItems = StockItem::where('enterprise_id', $enterpriseId)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $nextMonth)
            ->where('expiry_date', '>=', $today)
            ->orderBy('expiry_date', 'asc')
            ->limit(5)
            ->get(['name', 'expiry_date', 'current_quantity']);

        // Already expired items
        $expiredCount = StockItem::where('enterprise_id', $enterpriseId)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $today)
            ->count();

        // Total active stock items
        $totalActiveItems = StockItem::where('enterprise_id', $enterpriseId)
            ->where('current_quantity', '>', 0)
            ->count();

        // Total stock value (estimated)
        $totalStockValue = StockItem::where('enterprise_id', $enterpriseId)
            ->where('current_quantity', '>', 0)
            ->selectRaw('SUM(current_quantity * cost_per_unit) as total_value')
            ->value('total_value') ?? 0;

        // Recent stock movements (last 7 days)
        $recentStockOuts = \App\Models\StockOutRecord::where('enterprise_id', $enterpriseId)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        return [
            'low_stock_items' => $lowStockItems,
            'out_of_stock_count' => $outOfStockCount,
            'expiring_soon_items' => $expiringSoonItems,
            'expired_count' => $expiredCount,
            'total_active_items' => $totalActiveItems,
            'total_stock_value' => number_format($totalStockValue, 0),
            'recent_stock_outs' => $recentStockOuts,
            'low_stock_count' => $lowStockItems->count(),
            'expiring_soon_count' => $expiringSoonItems->count()
        ];
    }

    /**
     * Render the widget
     */
    public function render()
    {
        return view($this->view, [
            'data' => $this->data()
        ])->render();
    }
}
