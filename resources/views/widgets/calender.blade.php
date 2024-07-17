<?php
use App\Models\Utils;
?>
<div>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Calendar</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12 px-3">
                    <div class="clearfix"></div>
                    <div class="card card-primary">
                        <div class="card-body p-0">
                            <div id='calendar-container'>
                                <div id='calendar'></div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>

</div>


@push('third_party_stylesheets')
    <link href="{{ asset('assets/fullcalendar/main.min.css') }}" rel="stylesheet" />
@endpush

@push('third_party_scripts')
    <script src="{{ asset('assets/fullcalendar/main.min.js') }}"></script>
@endpush
@push('page_scripts')
    <script>
        $(function() {

            /* initialize the calendar
             -----------------------------------------------------------------*/
            //Date for the calendar events (dummy data)
            var date = new Date()
            var d = date.getDate(),
                m = date.getMonth(),
                y = date.getFullYear()

            Date.prototype.addDays = function(days) {
                var date = new Date(this.valueOf());
                date.setDate(date.getDate() + days);
                return date;
            }

            var Calendar = FullCalendar.Calendar;
            var Draggable = FullCalendar.Draggable;

            var containerEl = document.getElementById('external-events');
            var checkbox = document.getElementById('drop-remove');
            var calendarEl = document.getElementById('calendar');

            var data = @this.weekly_activities;
            var calendar = new Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                themeSystem: 'bootstrap',
                events: JSON.parse(data),

                eventClick: function(info) {

                    // alert('Event: ' + info.event.title);
                    // display a modal
                    // $('#modal-title').html(info.event.title);
                    // $('#modal-body').html(info.event.extendedProps.description);
                    // $('#eventUrl').attr('href',info.event.url);
                    // $('#modal-default').modal();
                    var modal = document.getElementById("modal-default");
                    var span = document.getElementsByClassName("close")[0];
                    modal.style.display = "block";
                    span.onclick = function() {
                        modal.style.display = "none";
                    }
                    window.onclick = function(event) {
                        if (event.target == modal) {
                            modal.style.display = "none";
                        }
                    }

                },
                dateClick: function(info) {
                    //added 1 day to the date because the calender is behind by 1 day
                    var date = info.date;
                    @this.redirectToKanbaan(date.addDays(1));
                },
                editable: true,
                droppable: true, // this allows things to be dropped onto the calendar !!!
                drop: function(info) {
                    //  remove the element from the "Draggable Events" list
                    info.draggedEl.parentNode.removeChild(info.draggedEl);
                },
                // eventDrop: info => @this.updateActivity(info.event, info.oldEvent),
                loading: function(isLoading) {
                    if (!isLoading) {
                        //Reset activities
                        this.getEvents().forEach(function(e) {
                            if (e.source === null) {
                                e.remove();
                            }
                        });
                    }
                }
            });

            calendar.render();
            @this.on('refreshCalendar', () => {
                calendar.refetchEvents();
            });

        })
    </script>
@endpush
