<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-calendar-check"></i>
            Appointment Overview
        </h3>
        <div class="card-tools">
            <a href="{{ admin_url('appointments/calendar') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-calendar-alt"></i> View Calendar
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Statistics Row -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $data['today'] }}</h3>
                        <p>Today's Appointments</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $data['tomorrow'] }}</h3>
                        <p>Tomorrow's Appointments</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $data['week'] }}</h3>
                        <p>This Week</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $data['month'] }}</h3>
                        <p>This Month</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status and Priority Breakdown -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h4 class="card-title">Today's Status Breakdown</h4>
                    </div>
                    <div class="card-body">
                        @if(empty($data['today_status']))
                            <p class="text-muted">No appointments scheduled for today</p>
                        @else
                            @foreach($data['today_status'] as $status => $count)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-{{ 
                                        $status === 'scheduled' ? 'primary' : (
                                        $status === 'confirmed' ? 'success' : (
                                        $status === 'cancelled' ? 'danger' : (
                                        $status === 'completed' ? 'info' : 'warning')))
                                    }}">
                                        {{ ucfirst($status ?? 'scheduled') }}
                                    </span>
                                    <span class="font-weight-bold">{{ $count }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h4 class="card-title">Today's Priority Breakdown</h4>
                    </div>
                    <div class="card-body">
                        @if(empty($data['today_priority']))
                            <p class="text-muted">No appointments scheduled for today</p>
                        @else
                            @foreach($data['today_priority'] as $priority => $count)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-{{ 
                                        $priority === 'low' ? 'success' : (
                                        $priority === 'normal' ? 'primary' : (
                                        $priority === 'high' ? 'warning' : 'danger'))
                                    }}">
                                        {{ ucfirst($priority ?? 'normal') }}
                                    </span>
                                    <span class="font-weight-bold">{{ $count }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h4 class="card-title">Upcoming Appointments (Next 7 Days)</h4>
                    </div>
                    <div class="card-body p-0">
                        @if($data['upcoming']->isEmpty())
                            <div class="p-3">
                                <p class="text-muted">No upcoming appointments</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Patient</th>
                                            <th>Doctor</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['upcoming'] as $appointment)
                                            <tr>
                                                <td>
                                                    <small>
                                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, H:i') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong>{{ $appointment->patient_name }}</strong>
                                                </td>
                                                <td>
                                                    <small>
                                                        Dr. {{ $appointment->doctor->name ?? 'Unknown' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-sm badge-{{ 
                                                        $appointment->appointment_status === 'scheduled' ? 'primary' : (
                                                        $appointment->appointment_status === 'confirmed' ? 'success' : 'warning')
                                                    }}">
                                                        {{ ucfirst($appointment->appointment_status ?? 'scheduled') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h4 class="card-title">Recent Completed</h4>
                    </div>
                    <div class="card-body p-0">
                        @if($data['recent_completed']->isEmpty())
                            <div class="p-3">
                                <p class="text-muted">No recent completed appointments</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Patient</th>
                                            <th>Doctor</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['recent_completed'] as $appointment)
                                            <tr>
                                                <td>
                                                    <small>
                                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, H:i') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong>{{ $appointment->patient_name }}</strong>
                                                </td>
                                                <td>
                                                    <small>
                                                        Dr. {{ $appointment->doctor->name ?? 'Unknown' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ ucfirst(str_replace('_', ' ', $appointment->appointment_type ?? 'consultation')) }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h4 class="card-title">Quick Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="{{ admin_url('consultations/create') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-plus"></i> New Appointment
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ admin_url('appointments/calendar') }}" class="btn btn-info btn-block">
                                    <i class="fas fa-calendar-alt"></i> View Calendar
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ admin_url('consultations') }}?appointment_status=scheduled" class="btn btn-warning btn-block">
                                    <i class="fas fa-clock"></i> Scheduled
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ admin_url('consultations') }}?appointment_status=confirmed" class="btn btn-success btn-block">
                                    <i class="fas fa-check"></i> Confirmed
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
