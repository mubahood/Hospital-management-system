<?php

namespace App\Admin\Controllers;

use App\Models\ProjectSection;
use App\Models\Task;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TaskController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected function title()
    {
        $title = 'Tasks';
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
        

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('assigned_to', __('Assigned To'))->select(\App\Models\User::where('company_id', auth()->user()->company_id)->pluck('name', 'id'));
            $filter->equal('manager_id', __('Supervisor'))->select(\App\Models\User::where('company_id', auth()->user()->company_id)->pluck('name', 'id'));
            $filter->equal('project_id', __('Project'))->select(\App\Models\Project::where('company_id', auth()->user()->company_id)->pluck('name', 'id'));
            $filter->equal('project_section_id', __('Project Section'))->select(\App\Models\ProjectSection::where('company_id', auth()->user()->company_id)->pluck('name', 'id'));
            $filter->equal('priority', __('Priority'))->select([
                'Low' => 'Low',
                'Medium' => 'Medium',
                'High' => 'High',
            ]);
            $filter->equal('delegate_submission_status', __('Delegate Submission Status'))->select([
                'Not Submitted' => 'Not Submitted',
                'Done' => 'Done',
                'Done Late' => 'Done Late',
                'Not Attended To' => 'Not Attended To',
            ]);
            $filter->equal('manager_submission_status', __('Supervisor Submission Status'))->select([
                'Not Submitted' => 'Not Submitted',
                'Done' => 'Done',
                'Done Late' => 'Done Late',
                'Not Attended To' => 'Not Attended To',
            ]);
            $filter->between('due_to_date', __('Due Date'))->date();
        });

        $grid->model()->where('company_id', auth()->user()->company_id)
            ->orderBy('due_to_date', 'desc');

        if (!auth()->user()->can('administrator')) {
            $grid->model()->where('manager_id', auth()->user()->id)
                ->orWhere('assigned_to', auth()->user()->id)
                ->orWhere('created_by', auth()->user()->id);
        }


        $grid->column('id', __('Id'))->hide()->sortable();
        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            })
            ->hide()
            ->sortable();


        $grid->column('name', __('Task'))->sortable();

        $grid->column('assigned_to', __('Assigned To'))
            ->display(function ($assigned_to) {
                $user = $this->assigned_to_user;
                if ($user == null) {
                    return "User not found";
                }
                return $user->name;
            })
            ->sortable();

        $grid->column('due_to_date', __('Due Date'))
            ->display(function ($due_to_date) {
                return Utils::my_date($due_to_date);
            })->sortable();


        $grid->column('delegate_submission_status', __('Delegate Submission'))
            ->label([
                'Not Submitted' => 'default',
                'Done' => 'success',
                'Missed' => 'danger',
            ])->sortable();
        $grid->column('delegate_submission_remarks', __('Delegate Remarks'))
            ->hide();
        $grid->column('manager_submission_status', __('Manager Submission'))
            ->label([
                'Not Submitted' => 'default',
                'Done' => 'success',
                'Missed' => 'danger',
            ])->sortable();
        $grid->column('manager_submission_remarks', __('Manager Remarks'))
            ->sortable();
        $grid->column('task_description', __('Task Details'))
            ->hide();
        $grid->column('project_id', __('Project'))
            ->display(function ($project_id) {
                $project = $this->project;
                if ($project == null) {
                    return "Project not found";
                }
                return $project->name;
            })
            ->sortable();

        $grid->column('priority', __('Priority'))
            ->dot([
                'Low' => 'default',
                'Medium' => 'warning',
                'High' => 'danger',
            ])
            ->filter([
                'Low' => 'Low',
                'Medium' => 'Medium',
                'High' => 'High',
            ])
            ->sortable();

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

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('project_id', __('Project id'));
        $show->field('project_section_id', __('Project section id'));
        $show->field('assigned_to', __('Assigned to'));
        $show->field('created_by', __('Created by'));
        $show->field('manager_id', __('Manager id'));
        $show->field('name', __('Name'));
        $show->field('task_description', __('Task description'));
        $show->field('due_to_date', __('Due to date'));
        $show->field('delegate_submission_status', __('Delegate submission status'));
        $show->field('delegate_submission_remarks', __('Delegate submission remarks'));
        $show->field('manager_submission_status', __('Manager submission status'));
        $show->field('manager_submission_remarks', __('Manager submission remarks'));

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
        $form->hidden('company_id', __('Company'))->value(auth()->user()->company_id);

        $conditions = [
            'company_id' => auth()->user()->company_id
        ];
        if (!auth()->user()->can('administrator')) {
            $conditions['managed_by'] = auth()->user()->id;
        }
        $users = \App\Models\User::where([])->pluck('name', 'id');
        $users[auth()->user()->id] = auth()->user()->name;
        $form->select('assigned_to', __('Assigned To'))
            ->options($users)
            ->default(auth()->user()->id)
            ->rules('required');
        $form->select('manager_id', __('Supervisor'))
            ->options($users)
            ->default(auth()->user()->id)
            ->rules('required');
        $form->hidden('project_section_id', __('Project Section'))->value(1);

        $form->hidden('created_by', __('Created by'))->value(auth()->user()->id);
        $form->text('name', __('Task Description'))->rules('required');
        $form->datetime('due_to_date', __('Due to date'))->default(date('Y-m-d H:i:s'))->rules('required');
        $form->radioCard('priority', __('Priority'))
            ->options([
                'Low' => 'Low',
                'Medium' => 'Medium',
                'High' => 'High',
            ])
            ->default('Medium')
            ->rules('required');
        $form->textarea('task_description', __('Task Details (Explanation)'));


        if ($form->isEditing()) {
            $exp = explode('/', request()->path());
            $model = Task::find($exp[1]);
            if ($model == null) {
                throw new \Exception("Task not found");
            }

            $form->divider('Task Submission');
            /* if ($model->assigned_to == auth()->user()->id) { */
            $form->radioCard('delegate_submission_status', __('Delegate Submission Status'))
                ->options([
                    'Not Submitted' => 'Not Submitted',
                    'Done' => 'Done',
                    'Done Late' => 'Done Late',
                    'Not Attended To' => 'Not Attended To',
                ]);
            $form->textarea('delegate_submission_remarks', __('Delegate Task Submission Remarks'));
            /*     } */

            $form->radioCard('manager_submission_status', __('Supervisor submission status'))
                ->options([
                    'Not Submitted' => 'Not Submitted',
                    'Done' => 'Done',
                    'Done Late' => 'Done Late',
                    'Not Attended To' => 'Not Attended To',
                ]);
            $form->textarea('manager_submission_remarks', __('Supervisor Task Submission Remarks'));
            /*           } */
        }

        //$form->disableEditingCheck();
        $form->disableViewCheck();
        $form->disableReset();
        return $form;
    }
}
