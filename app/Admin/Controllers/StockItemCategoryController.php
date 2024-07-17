<?php

namespace App\Admin\Controllers;

use App\Models\StockItemCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StockItemCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock Item Categories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StockItemCategory());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('name', __('Name'));
        $grid->column('description', __('Description'));
        $grid->column('measuring_unit', __('Measuring unit'));
        $grid->column('current_stock_value', __('Current stock value'));
        $grid->column('current_stock_quantity', __('Current stock quantity'));
        $grid->column('reorder_level', __('Reorder level'));
        $grid->column('status', __('Status'));

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
        $show = new Show(StockItemCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('measuring_unit', __('Measuring unit'));
        $show->field('current_stock_value', __('Current stock value'));
        $show->field('current_stock_quantity', __('Current stock quantity'));
        $show->field('reorder_level', __('Reorder level'));
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
        $form = new Form(new StockItemCategory());

        $form->text('name', __('Name'))->rules('required');
        $form->text('measuring_unit', __('Measuring unit'))->rules('required');
        $form->textarea('description', __('Description'));

        $form->radio('status', __('Category Status'))
            ->options([
                'Active' => 'Active',
                'Inactive' => 'Inactive',
            ])
            ->default('Active');

        return $form;
    }
}
