<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <i class="fa fa-dollar-sign text-success"></i> Financial Overview
        </h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="revenue-box text-center mb-3">
                    <h3 class="text-success">UGX {{ $data['monthly_revenue'] }}</h3>
                    <p class="text-muted mb-0">{{ $data['month_name'] }} Revenue</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="revenue-box text-center mb-3">
                    <h4 class="text-info">UGX {{ $data['weekly_revenue'] }}</h4>
                    <p class="text-muted mb-0">This Week</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="revenue-box text-center mb-3">
                    <h4 class="text-primary">UGX {{ $data['daily_revenue'] }}</h4>
                    <p class="text-muted mb-0">Today ({{ $data['today_date'] }})</p>
                </div>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-warning mb-2">
                    <strong>UGX {{ $data['pending_payments'] }}</strong> in pending payments
                    <br><small>{{ $data['pending_invoices_count'] }} outstanding invoices</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-info mb-2">
                    <strong>UGX {{ $data['avg_consultation_cost'] }}</strong> average consultation
                    <br><small>{{ $data['monthly_consultations'] }} consultations this month</small>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="financial-summary">
                    <small class="text-muted">
                        <i class="fa fa-chart-line"></i> 
                        Revenue per consultation: <strong>UGX {{ $data['revenue_per_consultation'] }}</strong>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.revenue-box {
    padding: 15px;
    border-radius: 8px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid #28a745;
}
.revenue-box h3, .revenue-box h4 {
    margin-bottom: 5px;
    font-weight: bold;
}
.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}
.card-header {
    background-color: #fff;
    border-bottom: 1px solid #e9ecef;
}
.alert {
    padding: 10px;
    border-radius: 5px;
}
.financial-summary {
    text-align: center;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
}
</style>
