<?php
use App\Models\Utils;
?>
<div class="card mb-4 mb-md-5 border-0">
    <div class="card-body py-2 py-md-3">
        <div id='loading'></div>
        <div id='calendar'></div>
    </div>
</div>

{{-- <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-5 p-md-4">
                <h3 class="fs-30 fw-800 mb-3">Notice Board</h3>
                <hr>
                <p>
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Quod ex impedit aliquam, voluptatum ipsa
                    laboriosam cum sint nisi ... <a href="javascript:;">Read More</a>
                </p>
            </div>
        </div>
    </div>
</div> --}}


<script>
    $(document).ready(function() {
        var data = JSON.parse('<?= json_encode($events) ?>');
        var calendarEl = document.getElementById('calendar');


        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'title',
                right: 'prev today next'
            },
            eventClick: function(arg) {
                arg.jsEvent.preventDefault()
                const eve = arg.event._def;
                const activity_id = eve.extendedProps.activity_id;
                $.alert({
                    title: eve.extendedProps.name + " - " + eve.extendedProps.status,
                    content: eve.extendedProps.details,
                    closeIcon: true,
                    buttons: {
                        view: {
                            btnClass: 'btn-primary btn-sm',
                            text: 'VIEW',
                            action: function() {
                                window.open(eve.extendedProps.url_view, '_blank');
                            }
                        },
                        edit: {
                            btnClass: 'btn-primary btn-sm',
                            text: 'UPDATE',
                            action: function() {
                                window.open(eve.extendedProps.url_edit, '_blank');
                            }
                        },

                        CLOSE: function() {

                        },
                    }
                });
            },
            editable: false,
            selectable: false,
            selectMirror: true,
            nowIndicator: true,
            displayEventTime: true,
            events: data,
            loading: function(bool) {
                document.getElementById('loading').style.display =
                    bool ? 'block' : 'none';
            }
        });
        calendar.render();
    });
</script>
