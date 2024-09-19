<?php
if (!isset($tasks_done)) {
    $tasks_done = 0;
}

if (!isset($events_count)) {
    $events_count = 0;
}
if (!isset($project_count)) {
    $project_count = 0;
}
if (!isset($tasks_count)) {
    $tasks_count = 0;
}
?>

<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-md-4">
                @include('widgets.box-6', [
                    'is_dark' => false,
                    'title' => 'Pending Consultations',
                    'icon' => 'box',
                    'number' => $pending_tasks_count,
                    'link' => admin_url('consultations'),
                ])
            </div>
            <div class="col-md-4">
                @include('widgets.box-6', [
                    'is_dark' => false,
                    'title' => 'Ongoing Consultations',
                    'icon' => 'list-task',
                    'number' => $ongoing_tasks_count,
                    'link' => admin_url('progress-monitoring?&main_status%5B%5D=Ongoing'),
                ])

            </div>
            <div class="col-md-4">
                @include('widgets.box-6', [
                    'is_dark' => true,
                    'title' => 'Pending<br>Tasks',
                    'icon' => 'calendar-event-fill',
                    'number' => $my_tasks_count,
                    'link' => admin_url('medical-services'),
                ])
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        @include('dashboard.consultations-payments', [
            'items' => $pending_for_payment,
            'title' => 'Pending payments',
        ])
    </div>
    <div class="col-md-6">
        @include('dashboard.tasks', [
            'items' => $ongoing_tasks,
            'title' => 'Pending tasks',
        ])
    </div>
</div>
