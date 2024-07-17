<?php

namespace App\Admin\Controllers;

use App\Models\Target;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TargetController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Targets & Goals';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Target());
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->model()->orderBy('id', 'desc');

        $grid->column('title', __('Title'))->sortable();
        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return date('d M Y', strtotime($created_at));
            })->hide();
        $grid->column('user_id', __('Team Lead'))
            ->display(function ($user_id) {
                return User::find($user_id)->name;
            })->sortable();
        $grid->column('project_id', __('Project'))
            ->display(function ($project_id) {
                return \App\Models\Project::find($project_id)->short_name;
            })->sortable();

        $grid->column('department_id', __('Department'))
            ->display(function ($department_id) {
                return \App\Models\Department::find($department_id)->name;
            })->sortable();

        $grid->column('type', __('Type'))->sortable()
            ->label([
                'Achievement' => 'primary',
                'Milestone' => 'success',
            ]);
        $grid->column('status', __('Status'))
            ->label([
                'Pending' => 'warning',
                'Completed' => 'success',
                'Missed' => 'danger',
            ])->sortable();
        $grid->column('priority', __('Priority'))->hide();
        $grid->column('description', __('Description'))->hide();
        $grid->column('due_date', __('Due Date'))
            ->display(function ($due_date) {
                return date('d M Y', strtotime($due_date));
            })->sortable();
        $grid->column('date_completed', __('Date Achieved'))
            ->display(function ($date_completed) {
                return date('d M Y', strtotime($date_completed));
            })->sortable();
        $grid->column('date_started', __('Date Started'))
            ->display(function ($date_started) {
                return date('d M Y', strtotime($date_started));
            })->sortable()
            ->hide();
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
        $show = new Show(Target::findOrFail($id));

        $show->field('created_at', __('Created'))
            ->as(function ($created_at) {
                return date('d M Y', strtotime($created_at));
            });
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('user_id', __('User id'));
        $show->field('project_id', __('Project id'));
        $show->field('department_id', __('Department id'));
        $show->field('title', __('Title'));
        $show->field('type', __('Type'));
        $show->field('status', __('Status'));
        $show->field('priority', __('Priority'));
        $show->field('description', __('Description'));
        $show->field('files', __('Files'));
        $show->field('photos', __('Photos'));
        $show->field('due_date', __('Due date'));
        $show->field('date_completed', __('Date completed'));
        $show->field('date_started', __('Date started'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Target());
        $u = Admin::user();
        $form->hidden('company_id', __('Company id'))->default($u->company_id);

        $form->text('title', __('Target Title'))->rules('required');
        $form->select('user_id', __('Team coordinator'))
            ->options(User::where('company_id', $u->company_id)->pluck('name', 'id'))
            ->required();
        //members
        $form->multipleSelect('members', __('Team Members'))
            ->options(User::where('company_id', $u->company_id)->pluck('name', 'id'));

        $form->select('project_id', __('Due to Project'))
            ->options(\App\Models\Project::where('company_id', $u->company_id)->pluck('name', 'id'))
            ->rules('required');
        $form->select('department_id', __('Main Department'))
            ->options(\App\Models\Department::where('company_id', $u->company_id)->pluck('name', 'id'))
            ->rules('required');

        $form->radio('type', __('Goal Type'))
            ->options([
                'Achievement' => 'Achievement',
                'Milestone' => 'Milestone',
            ])->rules('required');

        $form->date('date_started', __('Date started'))->rules('required');
        $form->date('due_date', __('Due Date'))->rules('required');



        $form->radio('priority', __('Priority'))
            ->options([
                'Normal' => 'Normal',
                'High' => 'High',
                'Urgent' => 'Urgent',
            ])->rules('required');

        $form->radioCard('status', __('Status'))
            ->options([
                'Pending' => 'Pending',
                'Completed' => 'Completed',
                'Missed' => 'Missed',
            ])->rules('required')
            ->when('Completed', function (Form $form) {
                $form->date('date_completed', __('Date completed'))->rules('required');
            });
        

        $form->multipleFile('files', __('Files'))
            ->removable()
            ->sortable()
            ->pathColumn('path')
            ->rules('mimes:doc,docx,pdf,xlsx,xls,ppt,pptx,txt,zip,rar');
        $form->multipleImage('photos', __('Photos'))
            ->removable()
            ->sortable()
            ->pathColumn('path')
            ->rules('mimes:png,jpg,jpeg,gif,svg');
        $form->quill('description', __('Description'));






        return $form;
    }
}
