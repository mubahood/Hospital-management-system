<?php

namespace App\Admin\Controllers;

use App\Models\DoseItem;
use App\Models\StockItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DoseItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Dose Items';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DoseItem());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('stock_item.name', __('Stock Item'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('dosage', __('Dosage'));
        $grid->column('frequency', __('Frequency'));
        $grid->column('instructions', __('Instructions'))->limit(50);
        $grid->column('created_at', __('Created At'))->display(function ($created_at) {
            return date('Y-m-d H:i', strtotime($created_at));
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
        $show = new Show(DoseItem::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('stock_item.name', __('Stock Item'));
        $show->field('quantity', __('Quantity'));
        $show->field('dosage', __('Dosage'));
        $show->field('frequency', __('Frequency'));
        $show->field('instructions', __('Instructions'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DoseItem());

        $form->select('stock_item_id', __('Stock Item'))
            ->options(StockItem::pluck('name', 'id'))
            ->required();
        
        $form->number('quantity', __('Quantity'))
            ->min(1)
            ->required();
            
        $form->text('dosage', __('Dosage'))
            ->placeholder('e.g., 500mg')
            ->required();
            
        $form->text('frequency', __('Frequency'))
            ->placeholder('e.g., Twice daily')
            ->required();
            
        $form->textarea('instructions', __('Instructions'))
            ->placeholder('Additional dosage instructions...');

        return $form;
    }
}
