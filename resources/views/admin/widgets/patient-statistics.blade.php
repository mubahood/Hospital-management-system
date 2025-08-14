<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <i class="fa fa-users text-primary"></i> Patient Statistics
        </h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="stat-box text-center mb-3">
                    <h3 class="text-primary">{{ $data['total_patients'] }}</h3>
                    <p class="text-muted mb-0">Total Patients</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-box text-center mb-3">
                    <h3 class="text-success">{{ $data['active_cases'] }}</h3>
                    <p class="text-muted mb-0">Active Cases</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="stat-box text-center">
                    <h4 class="text-info">{{ $data['new_patients_week'] }}</h4>
                    <small class="text-muted">New This Week</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box text-center">
                    <h4 class="text-warning">{{ $data['today_consultations'] }}</h4>
                    <small class="text-muted">Today's Visits</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box text-center">
                    <h4 class="text-secondary">{{ $data['pending_followups'] }}</h4>
                    <small class="text-muted">Pending Follow-ups</small>
                </div>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="fa fa-calendar"></i> {{ $data['new_patients_month'] }} new patients in {{ $data['month_name'] }}
                </small>
            </div>
            <div class="col-md-6 text-right">
                <small class="text-muted">
                    <i class="fa fa-check-circle"></i> {{ $data['completed_month'] }} completed consultations
                </small>
            </div>
        </div>
    </div>
</div>

<style>
.stat-box {
    padding: 10px;
    border-radius: 5px;
    background-color: #f8f9fa;
}
.stat-box h3, .stat-box h4 {
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
</style>
