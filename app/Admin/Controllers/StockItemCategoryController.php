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
        $grid->disableBatchActions();
        $grid->quickSearch('name');
        $grid->column('id', __('Id'))->sortable()->hide();
        $grid->model()->orderBy('name', 'asc');
        $grid->column('name', __('Name'))->sortable();
        $grid->column('description', __('Description'))->hide();
        $grid->column('measuring_unit', __('Measuring Unit'))->sortable();
        $grid->column('current_stock_quantity', __('Current stock quantity'))->sortable()->hide();
        $grid->column('current_stock_value', __('Current stock value'))->sortable()->hide();
        $grid->column('reorder_level', __('Reorder level'))->hide();
        $grid->column('status', __('Status'))->label([
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
