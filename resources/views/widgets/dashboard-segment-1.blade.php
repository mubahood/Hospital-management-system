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
                    'title' => 'Done Tasks',
                    'icon' => 'box',
                    'number' => $tasks_done,
                    'link' => admin_url('tasks?manager_submission_status=Done'),
                ])
            </div>
            <div class="col-md-4">
                @include('widgets.box-6', [
                    'is_dark' => false,
                    'title' => 'Not Attended To',
                    'icon' => 'list-task',
                    'number' => $tasks_missed,
                    'link' => admin_url('tasks?manager_submission_status=Not+Attended+To'),
                ])

            </div>
            <div class="col-md-4">
                @include('widgets.box-6', [
                    'is_dark' => true,
                    'title' => 'Not Submitted',
                    'icon' => 'calendar-event-fill',
                    'number' => $tasks_not_submitted,
                    'link' => admin_url('tasks-pending'),
                ])
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        @include('dashboard.tasks', [
            'items' => $pending_tasks,
            'title' => 'Pending tasks',
        ])
    </div>
    <div class="col-md-6">
        @include('dashboard.tasks', [
            'items' => $tasks_done_list,
            'title' => 'Completed tasks',
        ])
    </div>
</div>
