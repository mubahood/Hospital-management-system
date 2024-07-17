<?php

namespace App\Admin\Controllers;

use App\Models\Project;
use App\Models\ProjectSection;
use App\Models\Task;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use PharIo\Manifest\Author;

class TaskController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */

    //function for title
    function title()
    {
        //current url segments
        $segs = request()->segments();
        $title = "Tasks";
        if (in_array('tasks-pending', $segs)) {
            $title = "Pending Tasks";
        } else if (in_array('tasks-manage', $segs)) {
            $title = "Tasks supervised by me";
        } else if (in_array('tasks-completed', $segs)) {
            $title = "Completed Tasks";
        } else {
            $title = "Tasks";
        }
        return $title;
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Task());


        //$grid export 
        $grid->export(function ($export) {
            $export->filename('Tasks-' . date('Y-m-d'));

            //delegate_submission_status
            $export->originalValue(['delegate_submission_status']);
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('assigned_to', __('Assigned To'))->select(\App\Models\User::where('company_id', auth()->user()->company_id)->pluck('name', 'id'));
            $filter->equal('manager_id', __('Supervisor'))->select(\App\Models\User::where('company_id', auth()->user()->company_id)->pluck('name', 'id'));
            $filter->equal('project_id', __('Project'))->select(\App\Models\Project::where('company_id', auth()->user()->company_id)->pluck('name', 'id'));
            /*             $filter->equal('project_section_id', __('Project Section'))->select(\App\Models\ProjectSection::where('company_id', auth()->user()->company_id)->pluck('name', 'id')); */
            /*  $filter->equal('priority', __('Priority'))->select([
                'Low' => 'Low',
                'Medium' => 'Medium',
                'High' => 'High',
            ]); */
            $filter->equal('delegate_submission_status', __('Delegate Submission Status'))->select([
                'Not Submitted' => 'Not Submitted',
                'Done' => 'Done',
                'Done Late' => 'Done Late',
                'Not Attended To' => 'Not Attended To',
            ]);
            /*  $filter->equal('manager_submission_status', __('Supervisor Submission Status'))->select([
                'Not Submitted' => 'Not Submitted',
                'Done' => 'Done',
                'Done Late' => 'Done Late',
                'Not Attended To' => 'Not Attended To',
            ]); */
            $filter->between('due_to_date', __('Due Date'))->date();
        });
        $grid->disableBatchActions();


        $u = Admin::user();

        $segs = request()->segments();
        $is_submitted = 'Yes';

        if (in_array('tasks-pending', $segs)) {
            $is_submitted = 'No';
            if ($u->isRole('company-admin')) {
                $grid->model()->where([
                    'company_id' => $u->company_id,
                    'is_submitted' => $is_submitted,
                ])
                    ->orderBy('id', 'Desc');
            } else {
                $grid->model()->where([
                    'assigned_to' => $u->id,
                    'is_submitted' => $is_submitted,
                ])
                    ->orderBy('id', 'Desc');
            }
        } else if (in_array('tasks-manage', $segs)) {
            $grid->model()->where([
                'manager_id' => $u->id,
            ])
                ->orderBy('id', 'Desc');
        } else if (in_array('tasks-completed', $segs)) {
            $is_submitted = 'Yes';
            if ($u->isRole('company-admin')) {
                $grid->model()->where([
                    'company_id' => $u->company_id,
                    'is_submitted' => $is_submitted,
                ])
                    ->orderBy('id', 'Desc');
            } else {
                $grid->model()->where([
                    'assigned_to' => $u->id,
                    'is_submitted' => $is_submitted,
                ])
                    ->orderBy('id', 'Desc');
            }
        } else {
            if ($u->isRole('company-admin')) {
                $grid->model()->where([
                    'company_id' => $u->company_id,
                ])
                    ->orderBy('id', 'Desc');
            } else {
                $grid->model()->where([
                    'assigned_to' => $u->id,
                ])
                    ->orderBy('id', 'Desc');
            }
        }

        if (in_array('tasks-manage', $segs)) {
            $is_submitted = 'No';
        }

        if (in_array('tasks-completed', $segs)) {
            $is_submitted = 'Yes';
            $grid->model()->where([
                'assigned_to' => $u->id,
            ])
                ->orderBy('id', 'Desc');
        }



        $grid->quickSearch('name')->placeholder('Search by name or ID');
        //$grid->

        $grid->column('id', __('Id'))->sortable()->hide();
        $grid->column('due_to_date', __('Due Date'))
            ->display(function ($due_to_date) {
                return Utils::my_date($due_to_date);
            })->sortable();

        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            })
            ->hide()
            ->sortable();


        $grid->column('name', __('Task'))->sortable();

        $grid->column('task_description', __('Task Details'))
            ->sortable();

        $grid->column('project_id', __('Project'))
            ->display(function ($project_id) {
                $project = $this->project;
                if ($project == null) {
                    return "Project not found";
                }
                return $project->short_name;
            })
            ->sortable();

        $grid->column('assigned_to', __('Assigned To'))
            ->display(function ($assigned_to) {
                $user = $this->assigned_to_user;
                if ($user == null) {
                    return "User not found";
                }
                return $user->name;
            })
            ->sortable();

        $grid->column('manager_id', __('Supervisor'))
            ->display(function ($manager_id) {
                $user = $this->manager_user;
                if ($user == null) {
                    return "User not found";
                }
                return $user->name;
            })
            ->sortable()
            ->hide();


        $grid->column('delegate_submission_status', __('Submission Status'))
            ->label([
                'Not Submitted' => 'default',
                'Done' => 'success',
                'Not Attended To' => 'danger',
                'Done Late' => 'warning',
            ])->sortable();
        $grid->column('delegate_submission_remarks', __('Remarks'))->sortable();

        $grid->column('manager_submission_status', __('Supervisor Submission'))
            ->label([
                'Not Submitted' => 'default',
                'Done' => 'success',
                'Not Attended To' => 'danger',
                'Done Late' => 'warning',
            ])->sortable()->hide();
        $grid->column('manager_submission_remarks', __('Supervisor Remarks'))
            ->sortable()
            ->hide();



        $grid->column('priority', __('Priority'))
            ->sortable()->hide();

        $grid->column('created_by', __('Created By'))
            ->display(function ($created_by) {
                $user = $this->created_by_user;
                if ($user == null) {
                    return "User not found";
                }
                return $user->name;
            })
            ->hide()
            ->sortable();
        $grid->column('project_section_id', __('Project'))
            ->display(function ($project_section_id) {
                $project_section = $this->project_section;
                if ($project_section == null) {
                    return "Deliverable not found";
                }
                return $project_section->name_text;
            })
            ->hide()
            ->sortable();


        $grid->column('hours', __('Hours'))->sortable()->hide();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Task::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('created_at', __('Created'));
        $show->field('assigned_to', __('Assigned To'));
        $show->field('created_by', __('Created by'));
        $show->field('manager_id', __('Manager id'));
        $show->field('name', __('Name'));
        $show->field('task_description', __('Task description'));
        $show->field('due_to_date', __('Due to date'));
        $show->field('delegate_submission_status', __('Delegate submission status'));
        $show->field('delegate_submission_remarks', __('Delegate submission remarks'));
        $show->field('manager_submission_status', __('Manager submission status'));
        $show->field('manager_submission_remarks', __('Manager submission remarks'));
        $show->field('priority', __('Priority'));
        $show->field('meeting_id', __('Meeting id'));
        $show->field('rate', __('Rate'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Task());
        $u = Auth::user();
        $form->hidden('company_id', __('Company'))->default($u->company_id);
        $sections  = Project::get_array([
            'company_id' => $u->company_id,
        ]);

        $form->text('name', __('Task title'))->rules('required');
        $form->textarea('task_description', __('Task description'));
        /*         $form->hidden('hours', __('Hours'))->rules('required')
            ->help('Enter the number of hours you expect to spend on this task. (e.g. 1.5)')
            ->default(1); */

        $form->date('due_to_date', __('Due to date'))
            ->rules('required')
            ->help('Enter the date you expect to complete this task. (e.g. ' . date('Y-m-d') . ')');

        /*     $form->radio('priority', __('Priority'))->options([
            'Low' => 'Low',
            'Medium' => 'Medium',
            'High' => 'High',
        ])->default('Medium')->rules('required'); */


        $form->select('project_id', __('Due Project'))
            ->options($sections)->rules('required')->required();
        $form->radio('assign_to_type', 'Assign To?')->options([
            'to_me' => 'Assign To Me',
            'to_other' => 'Assign To Other',
        ])->when(
            'to_other',
            function (Form $form) {
                $form->select('assigned_to', __('Select Delegate'))
                    ->options(\App\Models\User::where('company_id', auth()->user()->company_id)->pluck('name', 'id'))
                    ->rules('required');
            }
        )->rules('required');

        if (!$form->isCreating()) {

            //get task id from url segments
            $segs = request()->segments();
            $task = null;

            if (isset($segs[1])) {
                $task_id = $segs[1];
                $task = Task::find($task_id);
                if ($task == null) {
                    if (isset($segs[2])) {
                        $task_id = $segs[2];
                        $task = Task::find($task_id);
                    }
                }
            }

            if ($task == null) {
                throw new \Exception('Task not found');
            }



            $form->divider('Task Status');
            if ($task->assigned_to == $u->id) {
                $form->radio('delegate_submission_status', 'Delegate Submission Status')->options([
                    'Not Submitted' => 'Not Submitted',
                    'Done' => 'Done',
                    'Done Late' => 'Done Late',
                    'Not Attended To' => 'Not Attended To',
                ])->default('Not Submitted')->rules('required');
                $form->text('delegate_submission_remarks', __('Delegate Remarks'));
            }
            if ($task->manager_id == $u->id) {
                $form->radio('manager_submission_status', 'Supervisor Submission Status')->options([
                    'Not Submitted' => 'Not Submitted',
                    'Done' => 'Done',
                    'Done Late' => 'Done Late',
                    'Not Attended To' => 'Not Attended To',
                ])->default('Not Submitted')->rules('required');
                $form->text('manager_submission_remarks', __('Manager Remarks'));
            }
        } else {
            $form->radio('delegate_submission_status', 'Delegate Submission Status')->options([
                'Not Submitted' => 'Not Submitted',
                'Done' => 'Done',
                'Done Late' => 'Done Late',
                'Not Attended To' => 'Not Attended To',
            ])->default('Not Submitted')->rules('required');
            $form->text('delegate_submission_remarks', __('Delegate Remarks'));
        }

        //disable delete button
        $form->disableViewCheck();
        $form->disableReset();
        return $form;
    }
}
