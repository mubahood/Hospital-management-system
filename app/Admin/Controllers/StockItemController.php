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

        $grid->disableBatchActions();
        $grid->model()->orderBy('name', 'asc');
        $grid->quickSearch('name', 'barcode')->placeholder('Search by name or barcode');
        $grid->column('id', __('Stock #ID'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('created_at', __('Added'))
            ->display(function ($date) {
                return \App\Models\Utils::my_date($date);
            })->sortable();
        $grid->column('stock_item_category_id', __('Category'))
            ->display(function ($id) {
                if ($this->category == null) {
                    return 'N/A';
                }
                return $this->category->name;
            })->sortable();
        $grid->column('original_quantity', __('Original Quantity'))
            ->display(function ($qty) {
                //if type is service, return N/A
                if ($this->type == 'Service') {
                    $unit = 'N/A';
                }
                //make $this->measuring_unit in plural if $qty > 1
                $unit = $this->measuring_unit;
                if ($qty > 1) {
                    $unit = \App\Models\Utils::pluralize($unit);
                }

                return $qty . ' ' . $unit;
            })->sortable();
        $grid->column('current_quantity', __('Current Quantity'))
            ->display(function ($qty) {
                //if type is service, return N/A
                if ($this->type == 'Service') {
                    $unit = 'N/A';
                }
                //make $this->measuring_unit in plural if $qty > 1
                $unit = $this->measuring_unit;
                if ($qty > 1) {
                    $unit = \App\Models\Utils::pluralize($unit);
                }

                return $qty . ' ' . $unit;
            })->sortable();
        $grid->column('description', __('Description'))->hide();
        $grid->column('current_stock_quantity', __('Current stock quantity'))->hide();
        $grid->column('reorder_level', __('Reorder level'))->hide();
        $grid->column('status', __('Status'))->label([
            'Active' => 'success',
            'Inactive' => 'danger',
        ])->sortable();
        $grid->column('purchase_price', __('Purchase Price (UGX)'))
            ->display(function ($price) {
                return number_format($price, 2);
            })->sortable();
        $grid->column('sale_price', __('Sell Price (UGX)'))
            ->display(function ($price) {
                return number_format($price, 2);
            })->sortable();
        $grid->column('barcode', __('Barcode'))->hide();
        $grid->column('supplier', __('Supplier'))->sortable();
        $grid->column('supplier_contact', __('Supplier Contact'))->sortable();
        $grid->column('supplier_address', __('Supplier address'))->hide();
        $grid->column('supplier_email', __('Supplier email'))->hide();
        $grid->column('supplier_phone', __('Supplier phone'))->hide();
        $grid->column('type', __('Type'))->hide();
        $grid->column('expire_date', __('Expiry Date'))->sortable();
        $grid->column('manufacture_date', __('Manufacture date'))->hide();

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
