<?php

namespace App\Admin\Controllers;

use App\Models\PaymentRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PaymentRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Payment Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PaymentRecord());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('consultation_id', __('Consultation id'));
        $grid->column('description', __('Description'));
        $grid->column('amount_payable', __('Amount payable'));
        $grid->column('amount_paid', __('Amount paid'));
        $grid->column('balance', __('Balance'));
        $grid->column('payment_date', __('Payment date'));
        $grid->column('payment_time', __('Payment time'));
        $grid->column('payment_method', __('Payment method'));
        $grid->column('payment_reference', __('Payment reference'));
        $grid->column('payment_status', __('Payment status'));
        $grid->column('payment_remarks', __('Payment remarks'));
        $grid->column('payment_phone_number', __('Payment phone number'));
        $grid->column('payment_channel', __('Payment channel'));

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
        $show = new Show(PaymentRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('consultation_id', __('Consultation id'));
        $show->field('description', __('Description'));
        $show->field('amount_payable', __('Amount payable'));
        $show->field('amount_paid', __('Amount paid'));
        $show->field('balance', __('Balance'));
        $show->field('payment_date', __('Payment date'));
        $show->field('payment_time', __('Payment time'));
        $show->field('payment_method', __('Payment method'));
        $show->field('payment_reference', __('Payment reference'));
        $show->field('payment_status', __('Payment status'));
        $show->field('payment_remarks', __('Payment remarks'));
        $show->field('payment_phone_number', __('Payment phone number'));
        $show->field('payment_channel', __('Payment channel'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PaymentRecord());

        $form->number('consultation_id', __('Consultation id'));
        $form->textarea('description', __('Description'));
        $form->decimal('amount_payable', __('Amount payable'));
        $form->decimal('amount_paid', __('Amount paid'));
        $form->decimal('balance', __('Balance'));
        $form->text('payment_date', __('Payment date'));
        $form->text('payment_time', __('Payment time'));
        $form->text('payment_method', __('Payment method'));
        $form->text('payment_reference', __('Payment reference'));
        $form->text('payment_status', __('Payment status'))->default('Pending');
        $form->textarea('payment_remarks', __('Payment remarks'));
        $form->text('payment_phone_number', __('Payment phone number'));
        $form->text('payment_channel', __('Payment channel'));

        return $form;
    }
}
