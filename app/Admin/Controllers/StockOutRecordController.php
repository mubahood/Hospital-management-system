<?php

namespace App\Admin\Controllers;

use App\Models\StockOutRecord;
use App\Models\Utils;
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
        $grid->model()->orderBy('id', 'desc');
        $grid->disableBatchActions();
        $grid->column('id', __('Sn.'))->sortable();
        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return Utils::my_date_time_1($created_at);
            })->sortable();
        $grid->column('stock_item_id', __('Stock item'))
            ->display(function ($stock_item_id) {
                if ($this->stock_item == null) {
                    return 'N/A';
                }
                return $this->stock_item->name;
            })->sortable();
        // $grid->column('stock_item_category_id', __('Stock Category'));
        $grid->column('unit_price', __('Unit Price'))
            ->display(function ($unit_price) {
                return number_format($unit_price, 2);
            })->sortable();
        $grid->column('quantity', __('Quantity'))
            ->display(function ($quantity) {
                return number_format($quantity, 0) . " " . $this->measuring_unit;
            })->sortable();
        $grid->column('total_price', __('Total price'))
            ->display(function ($total_price) {
                return number_format($total_price, 2);
            })->sortable();
        $grid->column('quantity_after', __('Quantity after'))
            ->display(function ($quantity_after) {
                return number_format($quantity_after, 2);
            })->sortable();
        $grid->column('description', __('Description'));
        // $grid->column('details', __('Details'));
        // $grid->column('', __('Measuring unit'));
        $grid->column('due_date', __('Due date'))->hide(); 

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
