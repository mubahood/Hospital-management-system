<?php

namespace App\Admin\Controllers;

use App\Models\StockOutRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StockOutRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock Out Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StockOutRecord());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('stock_item_id', __('Stock item id'));
        $grid->column('stock_item_category_id', __('Stock item category id'));
        $grid->column('unit_price', __('Unit price'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('total_price', __('Total price'));
        $grid->column('quantity_after', __('Quantity after'));
        $grid->column('description', __('Description'));
        $grid->column('details', __('Details'));
        $grid->column('measuring_unit', __('Measuring unit'));
        $grid->column('due_date', __('Due date'));

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
        $show = new Show(StockOutRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('stock_item_id', __('Stock item id'));
        $show->field('stock_item_category_id', __('Stock item category id'));
        $show->field('unit_price', __('Unit price'));
        $show->field('quantity', __('Quantity'));
        $show->field('total_price', __('Total price'));
        $show->field('quantity_after', __('Quantity after'));
        $show->field('description', __('Description'));
        $show->field('details', __('Details'));
        $show->field('measuring_unit', __('Measuring unit'));
        $show->field('due_date', __('Due date'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StockOutRecord());

        $form->select('stock_item_id', __('Stock item'))
            ->options(\App\Models\StockItem::getDropdownOptions())
            ->rules('required');
        $form->decimal('quantity', __('Quantity'))->rules('required');
        $form->textarea('description', __('Description'));
        $form->date('due_date', __('Due date'))->rules('required')->default(date('Y-m-d')); 
        return $form;
    }
}
