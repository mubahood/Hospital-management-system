<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <i class="fa fa-exclamation-triangle text-warning"></i> Inventory Alerts
        </h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-danger mb-3">
                    <h5 class="alert-heading">
                        <i class="fa fa-times-circle"></i> Critical Stock Levels
                    </h5>
                    <p class="mb-1">
                        <strong>{{ $data['out_of_stock_count'] }}</strong> items out of stock
                    </p>
                    <p class="mb-0">
                        <strong>{{ $data['low_stock_count'] }}</strong> items running low
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-warning mb-3">
                    <h5 class="alert-heading">
                        <i class="fa fa-clock"></i> Expiry Alerts
                    </h5>
                    <p class="mb-1">
                        <strong>{{ $data['expired_count'] }}</strong> items already expired
                    </p>
                    <p class="mb-0">
                        <strong>{{ $data['expiring_soon_count'] }}</strong> expiring within 30 days
                    </p>
                </div>
            </div>
        </div>
        
        @if($data['low_stock_items']->count() > 0)
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-danger">Low Stock Items:</h6>
                <div class="low-stock-list">
                    @foreach($data['low_stock_items'] as $item)
                    <div class="stock-item">
                        <span class="item-name">{{ $item->name }}</span>
                        <span class="stock-level text-danger">{{ $item->current_quantity }} {{ $item->unit }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @if($data['expiring_soon_items']->count() > 0)
            <div class="col-md-6">
                <h6 class="text-warning">Expiring Soon:</h6>
                <div class="expiring-list">
                    @foreach($data['expiring_soon_items'] as $item)
                    <div class="stock-item">
                        <span class="item-name">{{ $item->name }}</span>
                        <span class="expiry-date text-warning">{{ Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif
        
        <hr>
        
        <div class="row">
            <div class="col-md-4">
                <div class="inventory-stat text-center">
                    <h4 class="text-success">{{ $data['total_active_items'] }}</h4>
                    <small class="text-muted">Active Items</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="inventory-stat text-center">
                    <h4 class="text-info">UGX {{ $data['total_stock_value'] }}</h4>
                    <small class="text-muted">Stock Value</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="inventory-stat text-center">
                    <h4 class="text-primary">{{ $data['recent_stock_outs'] }}</h4>
                    <small class="text-muted">Recent Dispensings</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}
.card-header {
    background-color: #fff;
    border-bottom: 1px solid #e9ecef;
}
.stock-item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}
.stock-item:last-child {
    border-bottom: none;
}
.item-name {
    font-weight: 500;
}
.stock-level, .expiry-date {
    font-size: 0.9em;
    font-weight: bold;
}
.low-stock-list, .expiring-list {
    max-height: 150px;
    overflow-y: auto;
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
}
.inventory-stat {
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
}
.inventory-stat h4 {
    margin-bottom: 5px;
    font-weight: bold;
}
.alert-heading {
    margin-bottom: 10px;
}
</style>
