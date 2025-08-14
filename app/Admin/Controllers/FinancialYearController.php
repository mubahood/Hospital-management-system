<?php

namespace App\Admin\Controllers;

use App\Models\FinancialYear;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FinancialYearController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Financial Years';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FinancialYear());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'));
        $grid->column('start_date', __('Start Date'));
        $grid->column('end_date', __('End Date'));
        $grid->column('is_active', __('Active'))->display(function ($is_active) {
            return $is_active ? 'Yes' : 'No';
        })->label([
            1 => 'success',
            0 => 'danger',
        ]);
        $grid->column('created_at', __('Created At'))->display(function ($created_at) {
            return date('Y-m-d H:i', strtotime($created_at));
        });

        $grid->filter(function($filter) {
            $filter->like('name', 'Name');
            $filter->equal('is_active', 'Active')->select([0 => 'No', 1 => 'Yes']);
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
        $show = new Show(FinancialYear::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('Name'));
        $show->field('start_date', __('Start Date'));
        $show->field('end_date', __('End Date'));
        $show->field('is_active', __('Active'))->as(function ($is_active) {
            return $is_active ? 'Yes' : 'No';
        });
        $show->field('description', __('Description'));
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
        $form = new Form(new FinancialYear());

        $form->text('name', __('Name'))
            ->required()
            ->placeholder('e.g., FY 2024-2025');
            
        $form->date('start_date', __('Start Date'))
            ->required();
            
        $form->date('end_date', __('End Date'))
            ->required();
            
        $form->switch('is_active', __('Active'))
            ->default(0);
            
        $form->textarea('description', __('Description'))
            ->placeholder('Optional description for this financial year...');

        $form->saving(function (Form $form) {
            // Ensure only one financial year is active at a time
            if ($form->is_active) {
                FinancialYear::where('is_active', 1)->update(['is_active' => 0]);
            }
        });

        return $form;
    }
}
