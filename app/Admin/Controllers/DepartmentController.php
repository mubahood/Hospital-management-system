<?php

namespace App\Admin\Controllers;

use App\Models\Department;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DepartmentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Departments';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Department());
        $grid->disableBatchActions();
        $u = auth('admin')->user();
        $grid->model()
            ->where('company_id', $u->company_id)
            ->orderBy('name', 'asc');

        $grid->column('name', __('Name'))->sortable();
        $grid->column('head_of_department_id', __('Head of Department'))
            ->display(function ($head_of_department_id) {
                $head_of_department = $this->head_of_department;
                if ($head_of_department == null) return 'N/A';
                return $head_of_department->name;
            })
            ->sortable();
        $grid->column('description', __('Description'))->hide();

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
        $show = new Show(Department::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('head_of_department_id', __('Head of department id'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Department());
        $u = auth('admin')->user();
        $form->hidden('company_id', __('Company id'))->default($u->company_id);
        $form->text('name', __('Department Name'))->rules('required');
        $form->select('head_of_department_id', __('Head of Department'))
            ->options($u->company->employees()->pluck('name', 'id'))
            ->rules('required');
        $form->textarea('description', __('Description'));

        return $form;
    }
}
