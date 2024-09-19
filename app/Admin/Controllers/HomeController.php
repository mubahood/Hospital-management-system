<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Event;
use App\Models\MedicalService;
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

                $column->append(view('widgets.dashboard-segment-1', [
                    'pending_tasks_count' => Consultation::where([
                        'main_status' => 'Pending'
                    ])->count(),

                    'ongoing_tasks_count' => Consultation::where([
                        'main_status' => 'Ongoing'
                    ])->count(),

                    'my_tasks_count' => MedicalService::where([
                        "status" => "Pending"
                    ])->count(),
                    'pending_for_payment' => Consultation::where(
                        "total_due",
                        '>',
                        0
                    )->get(),
                    'ongoing_tasks' => MedicalService::where([
                        'status' => 'Pending'
                    ])->limit(10)
                        ->get()
                ]));
            });
            $row->column(6, function (Column $column) {

                $column->append(Dashboard::dashboard_calender());
            });
        });

        return $content;
    }
 
}
