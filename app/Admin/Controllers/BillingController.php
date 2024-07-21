<?php

namespace App\Admin\Controllers;

use App\Models\Consultation;
use App\Models\Service;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BillingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Consultations Billings';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Consultation());

        $grid->column('id', __('Id'));
        $grid->column('print-invoice', __('Invoice'))
            ->display(function ($id) {
                $url = url('print-invoice?id=' . $this->id);
                return '<a href="' . $url . '" target="_blank">Preview Invoice</a>';
            });
        $grid->column('generate-invoice', __('Re-Generate Invoice'))
            ->display(function ($id) {
                $url = url('regenerate-invoice?id=' . $this->id);
                return '<a href="' . $url . '" target="_blank">Re-Generate Invoice</a>';
            });

        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('patient_id', __('Patient id'));
        $grid->column('receptionist_id', __('Receptionist id'));
        $grid->column('company_id', __('Company id'));
        $grid->column('main_status', __('Main status'));
        $grid->column('patient_name', __('Patient name'));
        $grid->column('patient_contact', __('Patient contact'));
        $grid->column('contact_address', __('Contact address'));
        $grid->column('consultation_number', __('Consultation number'));
        $grid->column('preferred_date_and_time', __('Preferred date and time'));
        $grid->column('services_requested', __('Services requested'));
        $grid->column('reason_for_consultation', __('Reason for consultation'));
        $grid->column('main_remarks', __('Main remarks'));
        $grid->column('request_status', __('Request status'));
        $grid->column('request_date', __('Request date'));
        $grid->column('request_remarks', __('Request remarks'));
        $grid->column('receptionist_comment', __('Receptionist comment'));
        $grid->column('temperature', __('Temperature'));
        $grid->column('weight', __('Weight'));
        $grid->column('height', __('Height'));
        $grid->column('bmi', __('Bmi'));
        $grid->column('total_charges', __('Total charges'));
        $grid->column('total_paid', __('Total paid'));
        $grid->column('total_due', __('Total due'));
        $grid->column('payemnt_status', __('Payemnt status'));

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
        $show = new Show(Consultation::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('patient_id', __('Patient id'));
        $show->field('receptionist_id', __('Receptionist id'));
        $show->field('company_id', __('Company id'));
        $show->field('main_status', __('Main status'));
        $show->field('patient_name', __('Patient name'));
        $show->field('patient_contact', __('Patient contact'));
        $show->field('contact_address', __('Contact address'));
        $show->field('consultation_number', __('Consultation number'));
        $show->field('preferred_date_and_time', __('Preferred date and time'));
        $show->field('services_requested', __('Services requested'));
        $show->field('reason_for_consultation', __('Reason for consultation'));
        $show->field('main_remarks', __('Main remarks'));
        $show->field('request_status', __('Request status'));
        $show->field('request_date', __('Request date'));
        $show->field('request_remarks', __('Request remarks'));
        $show->field('receptionist_comment', __('Receptionist comment'));
        $show->field('temperature', __('Temperature'));
        $show->field('weight', __('Weight'));
        $show->field('height', __('Height'));
        $show->field('bmi', __('Bmi'));
        $show->field('total_charges', __('Total charges'));
        $show->field('total_paid', __('Total paid'));
        $show->field('total_due', __('Total due'));
        $show->field('payemnt_status', __('Payemnt status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Consultation());

        $form->divider('Consultation Details');

        $u = Admin::user();


        $form->display('patient_name', __('Patient'));
        $form->display('consultation_number', __('Consultation number'));

        $form->hidden('receptionist_id', __('Receptionist id'))->default($u->id);


        $form->divider('Medical Services Offered');

        $form->html('Consultation Details');
        $form->divider('More Billing Items');




        //has many billing_items
        $form->hasMany('billing_items', 'Add fees or charges', function (Form\NestedForm $form) {
            $form->radio('type', __('Billing Item Type'))
                ->options([
                    'Discount' => 'Discount',
                    'Fee' => 'Fee',
                    'Tax' => 'Tax',
                ])->rules('required');
            $form->text('description', __('Description'))->rules('required');
            $form->decimal('price', __('Amount (UGX)'))->rules('required');
        });

        $form->divider('Consultation Status');
        $form->radio('main_status', __('Update Consultation Stage'))
            ->options([
                'Approved' => 'Approved',
                'Ongoing' => 'Ongoing',
                'Billing' => 'Billing',
                'Payment' => 'Ready for Payment',
            ]);
        return $form;
    }
}
