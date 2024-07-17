<?php

namespace App\Admin\Controllers;

use App\Models\AdminRole;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class AdminRoleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Employees Roles/Positions';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $roleModel = config('admin.database.roles_model');
        $grid = new Grid(new $roleModel());
        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->model()->orderBy('name', 'desc');
        $grid->disableBatchActions();
        $grid->model()->where([
            'company_id' => Auth::user()->company_id
        ]);

        $grid->column('name', __('Position Name'))->sortable();
        $grid->column('permissions', trans('admin.permission'))->pluck('name')->label();

        $grid->column('slug', __('Slug'))->hide();


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
        $show = new Show(AdminRole::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {


        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $roleModel());

        $permissionModel = config('admin.database.permissions_model');
        $permissions = $permissionModel::all()->pluck('name', 'id');

        $form->text('name', __('Name'))->rules('required');
        $form->hidden('slug', __('Slug'))->default(time());
        $form->listbox('permissions', 'Permissions')
            ->options($permissions)
            ->rules('required');
        $form->hidden('company_id')->default(Auth::user()->company_id);


        return $form;
    }
}
