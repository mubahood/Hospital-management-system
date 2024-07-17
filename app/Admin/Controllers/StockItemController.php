<?php

namespace App\Admin\Controllers;

use App\Models\StockItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StockItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock Items';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StockItem());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('stock_item_category_id', __('Stock item category id'));
        $grid->column('name', __('Name'));
        $grid->column('original_quantity', __('Original quantity'));
        $grid->column('current_quantity', __('Current quantity'));
        $grid->column('current_stock_value', __('Current stock value'));
        $grid->column('description', __('Description'));
        $grid->column('current_stock_quantity', __('Current stock quantity'));
        $grid->column('reorder_level', __('Reorder level'));
        $grid->column('status', __('Status'));
        $grid->column('measuring_unit', __('Measuring unit'));
        $grid->column('purchase_price', __('Purchase price'));
        $grid->column('sale_price', __('Sale price'));
        $grid->column('barcode', __('Barcode'));
        $grid->column('supplier', __('Supplier'));
        $grid->column('supplier_contact', __('Supplier contact'));
        $grid->column('supplier_address', __('Supplier address'));
        $grid->column('supplier_email', __('Supplier email'));
        $grid->column('supplier_phone', __('Supplier phone'));
        $grid->column('type', __('Type'));
        $grid->column('expire_date', __('Expire date'));
        $grid->column('manufacture_date', __('Manufacture date'));

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
        $show = new Show(StockItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('stock_item_category_id', __('Stock item category id'));
        $show->field('name', __('Name'));
        $show->field('original_quantity', __('Original quantity'));
        $show->field('current_quantity', __('Current quantity'));
        $show->field('current_stock_value', __('Current stock value'));
        $show->field('description', __('Description'));
        $show->field('current_stock_quantity', __('Current stock quantity'));
        $show->field('reorder_level', __('Reorder level'));
        $show->field('status', __('Status'));
        $show->field('measuring_unit', __('Measuring unit'));
        $show->field('purchase_price', __('Purchase price'));
        $show->field('sale_price', __('Sale price'));
        $show->field('barcode', __('Barcode'));
        $show->field('supplier', __('Supplier'));
        $show->field('supplier_contact', __('Supplier contact'));
        $show->field('supplier_address', __('Supplier address'));
        $show->field('supplier_email', __('Supplier email'));
        $show->field('supplier_phone', __('Supplier phone'));
        $show->field('type', __('Type'));
        $show->field('expire_date', __('Expire date'));
        $show->field('manufacture_date', __('Manufacture date'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StockItem());


        $form->select('stock_item_category_id', __('Stock item category'))
            ->options(\App\Models\StockItemCategory::getDropdownOptions())
            ->rules('required');
        $form->text('name', __('Item Name'))->rules('required');
        $form->radio('type', __('Stock Type'))
            ->options([
                'Product' => 'Product',
                'Service' => 'Service',
            ])
            ->default('Product')
            ->rules('required');
        $form->decimal('original_quantity', __('Original quantity'))->rules('required');
        $form->decimal('sale_price', __('Unit selling price'))->rules('required');
        $form->decimal('purchase_price', __('Total Purchase Price'))->default(0.00);


        $form->divider('Details');
        $form->text('expire_date', __('Expire date'));
        $form->text('supplier_contact', __('Supplier contact'));
        $form->text('supplier_address', __('Supplier address'));
        $form->text('supplier_email', __('Supplier email'));
        $form->text('supplier_phone', __('Supplier phone'));
        $form->text('manufacture_date', __('Manufacture date'));
        $form->text('supplier', __('Supplier'));
        $form->textarea('description', __('Description'));

        return $form;
    }
}
