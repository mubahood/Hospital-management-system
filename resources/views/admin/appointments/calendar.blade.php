@extends('admin::index')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Appointment Calendar</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ admin_url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">Calendar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt"></i>
                                Appointment Schedule
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" onclick="location.href='{{ admin_url('consultations/create') }}'">
                                    <i class="fas fa-plus"></i> New Appointment
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Calendar filters -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="doctorFilter">Filter by Doctor:</label>
                                    <select id="doctorFilter" class="form-control">
                                        <option value="">All Doctors</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}">Dr. {{ $doctor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="statusFilter">Filter by Status:</label>
                                    <select id="statusFilter" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="completed">Completed</option>
                                        <option value="no-show">No Show</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="typeFilter">Filter by Type:</label>
                                    <select id="typeFilter" class="form-control">
                                        <option value="">All Types</option>
                                        <option value="consultation">Consultation</option>
                                        <option value="follow_up">Follow-up</option>
                                        <option value="check_up">Check-up</option>
                                        <option value="procedure">Procedure</option>
                                        <option value="emergency">Emergency</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button class="btn btn-info btn-block" onclick="refreshCalendar()">
                                            <i class="fas fa-sync"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Calendar container -->
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Legend</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <span class="badge badge-primary">●</span> Scheduled
                                </div>
                                <div class="col-md-2">
                                    <span class="badge badge-success">●</span> Confirmed
                                </div>
                                <div class="col-md-2">
                                    <span class="badge badge-danger">●</span> Cancelled
                                </div>
                                <div class="col-md-2">
                                    <span class="badge badge-info">●</span> Completed
                                </div>
                                <div class="col-md-2">
                                    <span class="badge badge-warning">●</span> No Show
                                </div>
                                <div class="col-md-2">
                                    <span class="badge badge-dark">●</span> Emergency
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Appointment Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Appointment Details</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="appointmentDetails">
                    <!-- Details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editAppointment">Edit</button>
                <button type="button" class="btn btn-success" id="confirmAppointment">Confirm</button>
                <button type="button" class="btn btn-danger" id="cancelAppointment">Cancel</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        height: 'auto',
        events: function(fetchInfo, successCallback, failureCallback) {
            // Fetch appointments from server
            fetch('/admin/api/appointments/calendar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    start: fetchInfo.startStr,
                    end: fetchInfo.endStr,
                    doctor_id: document.getElementById('doctorFilter').value,
                    status: document.getElementById('statusFilter').value,
                    type: document.getElementById('typeFilter').value
                })
            })
            .then(response => response.json())
            .then(data => {
                successCallback(data.map(appointment => {
                    return {
                        id: appointment.id,
                        title: appointment.title,
                        start: appointment.start,
                        end: appointment.end,
                        backgroundColor: getStatusColor(appointment.status),
                        borderColor: getPriorityColor(appointment.priority),
                        extendedProps: appointment
                    };
                }));
            })
            .catch(error => {
                console.error('Error fetching appointments:', error);
                failureCallback(error);
            });
        },
        eventClick: function(info) {
            showAppointmentDetails(info.event.extendedProps);
        },
        dateClick: function(info) {
            // Quick appointment creation
            var url = '{{ admin_url("consultations/create") }}?appointment_date=' + info.dateStr;
            window.location.href = url;
        },
        eventDidMount: function(info) {
            // Add tooltips
            $(info.el).tooltip({
                title: info.event.extendedProps.tooltip,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        }
    });

    calendar.render();

    // Store calendar reference globally
    window.appointmentCalendar = calendar;
});

function getStatusColor(status) {
    const colors = {
        'scheduled': '#007bff',
        'confirmed': '#28a745',
        'cancelled': '#dc3545',
        'completed': '#17a2b8',
        'no-show': '#ffc107'
    };
    return colors[status] || '#6c757d';
}

function getPriorityColor(priority) {
    const colors = {
        'low': '#28a745',
        'normal': '#007bff',
        'high': '#ffc107',
        'urgent': '#dc3545'
    };
    return colors[priority] || '#007bff';
}

function refreshCalendar() {
    if (window.appointmentCalendar) {
        window.appointmentCalendar.refetchEvents();
    }
}

function showAppointmentDetails(appointment) {
    const details = `
        <div class="row">
            <div class="col-md-6">
                <h6>Patient Information</h6>
                <p><strong>Name:</strong> ${appointment.patient_name}</p>
                <p><strong>Contact:</strong> ${appointment.patient_contact}</p>
                <p><strong>Address:</strong> ${appointment.contact_address || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <h6>Appointment Details</h6>
                <p><strong>Doctor:</strong> Dr. ${appointment.doctor_name}</p>
                <p><strong>Type:</strong> ${appointment.type}</p>
                <p><strong>Status:</strong> <span class="badge badge-${getStatusBadgeClass(appointment.status)}">${appointment.status}</span></p>
                <p><strong>Priority:</strong> <span class="badge badge-${getPriorityBadgeClass(appointment.priority)}">${appointment.priority}</span></p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Consultation Details</h6>
                <p>${appointment.reason_for_consultation || 'N/A'}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>Notes</h6>
                <p>${appointment.appointment_notes || 'No notes'}</p>
            </div>
        </div>
    `;
    
    document.getElementById('appointmentDetails').innerHTML = details;
    
    // Set up action buttons
    document.getElementById('editAppointment').onclick = function() {
        window.location.href = '{{ admin_url("consultations") }}/' + appointment.id + '/edit';
    };
    
    document.getElementById('confirmAppointment').onclick = function() {
        updateAppointmentStatus(appointment.id, 'confirmed');
    };
    
    document.getElementById('cancelAppointment').onclick = function() {
        if (confirm('Are you sure you want to cancel this appointment?')) {
            updateAppointmentStatus(appointment.id, 'cancelled');
        }
    };
    
    $('#appointmentModal').modal('show');
}

function getStatusBadgeClass(status) {
    const classes = {
        'scheduled': 'primary',
        'confirmed': 'success',
        'cancelled': 'danger',
        'completed': 'info',
        'no-show': 'warning'
    };
    return classes[status] || 'secondary';
}

function getPriorityBadgeClass(priority) {
    const classes = {
        'low': 'success',
        'normal': 'primary',
        'high': 'warning',
        'urgent': 'danger'
    };
    return classes[priority] || 'primary';
}

function updateAppointmentStatus(appointmentId, newStatus) {
    fetch('/admin/api/appointments/' + appointmentId + '/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#appointmentModal').modal('hide');
            refreshCalendar();
            toastr.success('Appointment status updated successfully!');
        } else {
            toastr.error('Error updating appointment status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Error updating appointment status');
    });
}

// Filter change handlers
document.getElementById('doctorFilter').onchange = refreshCalendar;
document.getElementById('statusFilter').onchange = refreshCalendar;
document.getElementById('typeFilter').onchange = refreshCalendar;
</script>
@endsection

@section('style')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
.fc-event {
    cursor: pointer;
}
.fc-event:hover {
    opacity: 0.8;
}
.fc-daygrid-event {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fc-toolbar-title {
    font-size: 1.2em;
}
</style>
@endsection
