<?php

namespace App\Admin\Controllers;

use App\Models\DoseItemRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DoseItemRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DoseItemRecord';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DoseItemRecord());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('consultation_id', __('Consultation id'));
        $grid->column('medicine', __('Medicine'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('units', __('Units'));
        $grid->column('times_per_day', __('Times per day'));
        $grid->column('number_of_days', __('Number of days'));
        $grid->column('status', __('Status'));
        $grid->column('remarks', __('Remarks'));
        $grid->column('due_date', __('Due date'));
        $grid->column('date_submitted', __('Date submitted'));

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
        $show = new Show(DoseItemRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('consultation_id', __('Consultation id'));
        $show->field('medicine', __('Medicine'));
        $show->field('quantity', __('Quantity'));
        $show->field('units', __('Units'));
        $show->field('times_per_day', __('Times per day'));
        $show->field('number_of_days', __('Number of days'));
        $show->field('status', __('Status'));
        $show->field('remarks', __('Remarks'));
        $show->field('due_date', __('Due date'));
        $show->field('date_submitted', __('Date submitted'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DoseItemRecord());

        $form->number('consultation_id', __('Consultation id'));
        $form->textarea('medicine', __('Medicine'));
        $form->number('quantity', __('Quantity'));
        $form->textarea('units', __('Units'));
        $form->number('times_per_day', __('Times per day'));
        $form->number('number_of_days', __('Number of days'));
        $form->text('status', __('Status'))->default('Not taken');
        $form->textarea('remarks', __('Remarks'));
        $form->datetime('due_date', __('Due date'))->default(date('Y-m-d H:i:s'));
        $form->datetime('date_submitted', __('Date submitted'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
