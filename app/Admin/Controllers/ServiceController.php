<?php

namespace App\Admin\Controllers;

use App\Models\Service;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ServiceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Service';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Service());
        $grid->disableBatchActions();
        $grid->model()->orderBy('name', 'asc');
        $grid->column('name', __('Name'))->sortable();
        //price
        $grid->column('price', __('Price'))->display(function ($price) {
            return 'UGX ' . number_format($price, 0);
        })->sortable();
        $grid->column('description', __('Description'))->hide();
        $grid->column('status', __('Status'))
            ->label([
                'Active' => 'success',
                'Inactive' => 'danger',
            ])->sortable();

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
        $show = new Show(Service::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Service());

        $form->text('name', __('Name'))->rules('required');
        $form->text('price', __('Price'))->rules('required');
        $form->radio('status', __('Status'))
            ->options([
                'Active' => 'Active',
                'Inactive' => 'Inactive',
            ])
            ->default('Active');
        $form->textarea('description', __('Description'));

        return $form;
    }
}
