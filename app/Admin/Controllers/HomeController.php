<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Auth;
use SplFileObject;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        $admin = Auth::user();
        $u = Admin::user();

        $content
            ->title('<b>' . Utils::greet() . " " . $u->last_name . '!</b>');

        $content->row(function (Row $row) {
            $row->column(6, function (Column $column) {
                $u = Admin::user();
                $tasks_done_condition = [
                    'is_submitted' => 'Yes',
                    'manager_submission_status' => 'Done'
                ];

                $tasks_done_late_condition = [
                    'is_submitted' => 'Yes',
                    'manager_submission_status' => 'Done Late'
                ];

                $tasks_missed_condition = [
                    'is_submitted' => 'Yes',
                    'manager_submission_status' => 'Not Attended To'
                ];

                $tasks_not_submitted_condition = [
                    'is_submitted' => 'No',
                ];


                if ($u->isRole('company-admin')) {
                    $tasks_done_condition['company_id'] = $u->company_id;
                    $tasks_missed_condition['company_id'] = $u->company_id;
                    $tasks_done_late_condition['company_id'] = $u->company_id;
                    $tasks_not_submitted_condition['company_id'] = $u->company_id;
                } else {
                    $tasks_done_condition['assigned_to'] = $u->id;
                    $tasks_missed_condition['assigned_to'] = $u->id;
                    $tasks_done_late_condition['assigned_to'] = $u->id;
                    $tasks_not_submitted_condition['assigned_to'] = $u->id;
                }

                $start_of_week = Carbon::now()->startOfMonth();
                $end_of_week = Carbon::now()->endOfMonth();

                $events = Utils::prepare_calendar_tasks($u);
                $column->append(view('widgets.dashboard-segment-1', [
                    'events' => $events,
                    'tasks_done' => (Task::where($tasks_done_condition)
                        ->whereBetween('updated_at', [$start_of_week, $end_of_week])
                        ->count() +
                        Task::where($tasks_done_late_condition)
                        ->whereBetween('updated_at', [$start_of_week, $end_of_week])
                        ->count()),
                    'tasks_missed' => Task::where($tasks_missed_condition)
                        ->whereBetween('updated_at', [$start_of_week, $end_of_week])
                        ->count(),
                    'tasks_not_submitted' => Task::where($tasks_not_submitted_condition)
                        ->count(),
                    'meetings' => Meeting::where([
                        'company_id' => $u->company_id,
                    ])->where(
                        [/* 'event_date', '>=', Carbon::now()->format('Y-m-d') */]
                    )->orderBy('id', 'desc')->limit(5)->get(),
                    'pending_tasks' => Task::where($tasks_not_submitted_condition)
                        ->orderBy('id', 'desc')->limit(5)->get(),
                    'tasks_done_list' => Task::where($tasks_done_condition)
                        ->orderBy('id', 'desc')->limit(5)->get()
                ]));
            });
            $row->column(6, function (Column $column) {

                $column->append(Dashboard::dashboard_calender());
            });
        });

        return $content;
    }

    public function calendar(Content $content)
    {
        $u = Auth::user();
        $content
            ->title('Company Calendar');
        $content->row(function (Row $row) {
            $row->column(8, function (Column $column) {
                $column->append(Dashboard::dashboard_calender());
            });
            $row->column(4, function (Column $column) {
                $u = Admin::user();
                $column->append(view('dashboard.upcoming-events', [
                    'items' => Meeting::where([])
                        ->where([/* 'event_date', '>=', Carbon::now()->format('Y-m-d') */])
                        ->orderBy('id', 'desc')->limit(8)->get()
                ]));
            });
        });
        return $content;


        return $content;
    }
}
