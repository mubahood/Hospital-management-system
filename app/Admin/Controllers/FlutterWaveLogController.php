<?php

namespace App\Admin\Controllers;

use App\Models\FlutterWaveLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FlutterWaveLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'FlutterWaveLog';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FlutterWaveLog());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('consultation_id', __('Consultation id'));
        $grid->column('status', __('Status'));
        $grid->column('flutterwave_reference', __('Flutterwave reference'));
        $grid->column('flutterwave_payment_type', __('Flutterwave payment type'));
        $grid->column('flutterwave_payment_status', __('Flutterwave payment status'));
        $grid->column('flutterwave_payment_message', __('Flutterwave payment message'));
        $grid->column('flutterwave_payment_code', __('Flutterwave payment code'));
        $grid->column('flutterwave_payment_data', __('Flutterwave payment data'));
        $grid->column('flutterwave_payment_link', __('Flutterwave payment link'));
        $grid->column('flutterwave_payment_amount', __('Flutterwave payment amount'));
        $grid->column('flutterwave_payment_customer_name', __('Flutterwave payment customer name'));
        $grid->column('flutterwave_payment_customer_id', __('Flutterwave payment customer id'));
        $grid->column('flutterwave_payment_customer_email', __('Flutterwave payment customer email'));
        $grid->column('flutterwave_payment_customer_phone_number', __('Flutterwave payment customer phone number'));
        $grid->column('flutterwave_payment_customer_full_name', __('Flutterwave payment customer full name'));
        $grid->column('flutterwave_payment_customer_created_at', __('Flutterwave payment customer created at'));

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
        $show = new Show(FlutterWaveLog::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('consultation_id', __('Consultation id'));
        $show->field('status', __('Status'));
        $show->field('flutterwave_reference', __('Flutterwave reference'));
        $show->field('flutterwave_payment_type', __('Flutterwave payment type'));
        $show->field('flutterwave_payment_status', __('Flutterwave payment status'));
        $show->field('flutterwave_payment_message', __('Flutterwave payment message'));
        $show->field('flutterwave_payment_code', __('Flutterwave payment code'));
        $show->field('flutterwave_payment_data', __('Flutterwave payment data'));
        $show->field('flutterwave_payment_link', __('Flutterwave payment link'));
        $show->field('flutterwave_payment_amount', __('Flutterwave payment amount'));
        $show->field('flutterwave_payment_customer_name', __('Flutterwave payment customer name'));
        $show->field('flutterwave_payment_customer_id', __('Flutterwave payment customer id'));
        $show->field('flutterwave_payment_customer_email', __('Flutterwave payment customer email'));
        $show->field('flutterwave_payment_customer_phone_number', __('Flutterwave payment customer phone number'));
        $show->field('flutterwave_payment_customer_full_name', __('Flutterwave payment customer full name'));
        $show->field('flutterwave_payment_customer_created_at', __('Flutterwave payment customer created at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FlutterWaveLog());

        $form->number('consultation_id', __('Consultation id'));
        $form->text('status', __('Status'))->default('Pending');
        $form->textarea('flutterwave_reference', __('Flutterwave reference'));
        $form->textarea('flutterwave_payment_type', __('Flutterwave payment type'));
        $form->textarea('flutterwave_payment_status', __('Flutterwave payment status'));
        $form->textarea('flutterwave_payment_message', __('Flutterwave payment message'));
        $form->textarea('flutterwave_payment_code', __('Flutterwave payment code'));
        $form->textarea('flutterwave_payment_data', __('Flutterwave payment data'));
        $form->textarea('flutterwave_payment_link', __('Flutterwave payment link'));
        $form->textarea('flutterwave_payment_amount', __('Flutterwave payment amount'));
        $form->textarea('flutterwave_payment_customer_name', __('Flutterwave payment customer name'));
        $form->textarea('flutterwave_payment_customer_id', __('Flutterwave payment customer id'));
        $form->textarea('flutterwave_payment_customer_email', __('Flutterwave payment customer email'));
        $form->textarea('flutterwave_payment_customer_phone_number', __('Flutterwave payment customer phone number'));
        $form->textarea('flutterwave_payment_customer_full_name', __('Flutterwave payment customer full name'));
        $form->textarea('flutterwave_payment_customer_created_at', __('Flutterwave payment customer created at'));

        return $form;
    }
}
