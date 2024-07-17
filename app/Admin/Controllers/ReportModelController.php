<?php

namespace App\Admin\Controllers;

use App\Models\ReportModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ReportModelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Reports';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportModel());
        $grid->model()->where('company_id', \Encore\Admin\Facades\Admin::user()->company_id);

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            });
        $grid->column('user_id', __('Employee'))->display(function ($user_id) {
            $U = \App\Models\User::find($user_id);
            return $U ? $U->name : 'N/A';
        });
        $grid->column('department_id', __('Department'))
            ->display(function ($department_id) {
                $D =  \App\Models\Department::find($department_id);
                return $D ? $D->name : 'N/A';
            });
        $grid->column('type', __('Type'))
            ->display(function ($type) {
                return $type == 'Department' ? 'Department' : 'Employee';
            })
            ->label([
                'Department' => 'success',
                'Employee' => 'info',
            ])
            ->sortable();
        $grid->column('title', __('Title'));
        $grid->column('start_date', __('Start Date'))
            ->display(function ($start_date) {
                return date('d-m-Y', strtotime($start_date));
            })->sortable();
        $grid->column('end_date', __('End date'))
            ->display(function ($end_date) {
                return date('d-m-Y', strtotime($end_date));
            })->sortable();
        $grid->column('pdf_file', __('Print'))
            ->display(function ($pdf_file) {
                $url = url('report?id=' . $this->id);
                return "<a href='$url' target='_blank'>Print</a>";
            });

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
        $show = new Show(ReportModel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('user_id', __('User id'));
        $show->field('project_id', __('Project id'));
        $show->field('department_id', __('Department id'));
        $show->field('type', __('Type'));
        $show->field('title', __('Title'));
        $show->field('date_rage_type', __('Date rage type'));
        $show->field('date_range', __('Date range'));
        $show->field('generated', __('Generated'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
        $show->field('pdf_file', __('Pdf file'));
        $show->field('other_id', __('Other id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ReportModel());
        $u = \Encore\Admin\Facades\Admin::user();
        $form->hidden('company_id', __('Company'))->default($u->company_id);

        $form->radioCard('type', __('Report Type'))
            ->options([
                'Department' => 'Department',
                'User' => 'Employee'
            ])->rules('required')
            /*  ->when('Project', function (Form $form) {
                $form->select('project_id', __('Project'))->options(\App\Models\Project::where('company_id', \Encore\Admin\Facades\Admin::user()->company_id)->pluck('name', 'id'))->rules('required');
            }) */
            ->when('Department', function (Form $form) {
                $form->select('department_id', __('Department'))->options(\App\Models\Department::where('company_id', \Encore\Admin\Facades\Admin::user()->company_id)->pluck('name', 'id'))->rules('required');
            })
            ->when('User', function (Form $form) {
                $form->select('user_id', __('User'))->options(\App\Models\User::where('company_id', \Encore\Admin\Facades\Admin::user()->company_id)->pluck('name', 'id'))->rules('required');
            });


        $form->radio('date_rage_type', __('Date range type'))
            ->options([
                'Custom' => 'Custom',
                'Last 7 Days' => 'Last 7 Days',
                'Last 30 Days' => 'Last 30 Days',
                'Last 90 Days' => 'Last 90 Days',
                'Last 180 Days' => 'Last 180 Days',
                'Last 365 Days' => 'Last 365 Days',
                'This Month' => 'This Month',
                'Last Month' => 'Last Month',
                'This Year' => 'This Year',
                'Last Year' => 'Last Year',
            ])->default('Custom')->rules('required')
            ->when('Custom', function (Form $form) {
                $form->dateRange('start_date', 'end_date', 'Date range')->rules('required');
            });

        /* $form->text('generated', __('Generated'))->default('No');
        $form->textarea('pdf_file', __('Pdf file')); */


        /* 
        $form->textarea('title', __('Title'));
        $form->number('other_id', __('Other id'));
                */

        return $form;
    }
}
