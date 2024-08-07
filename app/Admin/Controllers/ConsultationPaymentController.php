<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Consultation\AddBillAction as ConsultationAddBillAction;
use App\Admin\Actions\Consultation\ViewBillAction;
use App\Models\Consultation;
use App\Models\Service;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ConsultationPaymentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Consultations Payments';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Consultation());

        //add this AddBillAction row action
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $actions->add(new ConsultationAddBillAction);
            $actions->add(new ViewBillAction);
        });


        //on export, export consultation_number as original
        $grid->export(function ($export) {
            $export->column('consultation_number', function ($value, $original) {
                return $original;
            });
        });


        $grid->disableBatchActions();
        $grid->model()->where([
            'main_status' => 'Payment',
        ])->orderBy('id', 'desc');
        $grid->quickSearch('patient_name', 'patient_contact')->placeholder('Search by name or contact');
        $grid->column('id', __('Id'))->sortable()->hide();

        $grid->column('created_at', __('Date'))
            ->display(function ($date) {
                return Utils::my_date_time($date);
            })->sortable();
        $grid->column('consultation_number', __('Consultation number'))
            ->sortable()
            ->display(function ($x) {
                $link = admin_url('consultations/' . $this->id);
                return "<a href='$link' title='View Consultation Details'><b>$x</b></a>";
            });


        $grid->column('updated_at', __('Updated'))
            ->display(function ($date) {
                return Utils::my_date_time($date);
            })->sortable()
            ->hide();
        $grid->column('patient_id', __('Patient'))
            ->display(function ($id) {
                if ($this->patient == null) {
                    return 'N/A';
                }
                return $this->patient->name;
            })->sortable();
        $grid->column('receptionist_id', __('Receptionist'))
            ->display(function ($id) {
                if ($this->receptionist == null) {
                    return 'N/A';
                }
                return $this->receptionist->name;
            })->sortable()->hide();;


        $grid->column('patient_contact', __('Contact'));
        $grid->column('contact_address', __('Address'))->sortable()->hide();
        $grid->column('preferred_date_and_time', __('Consultation Date'))
            ->sortable()
            ->hide();
        $grid->column('services_requested', __('Services Requested'))
            ->sortable()->hide();
        $grid->column('reason_for_consultation', __('Reason for consultation'))
            ->sortable()
            ->hide();
        // $grid->column('request_status', __('Request status'));
        // $grid->column('request_date', __('Request Date'));
        // $grid->column('request_remarks', __('Request Remarks'));
        // $grid->column('receptionist_comment', __('Receptionist comment'));
        $grid->column('temperature', __('Temperature'))->sortable()->hide();
        $grid->column('weight', __('Weight'))->sortable()->hide();
        $grid->column('height', __('Height'))->sortable()->hide();
        $grid->column('bmi', __('Bmi'))->sortable()->hide();
        $grid->column('services_text', __('Services'))->limit(50);



        $grid->column('total_charges', __('Total Amount (UGX)'))
            ->display(function ($id) {
                return number_format($id);
            })->sortable();

        $grid->column('main_status', __('Consultation Stage'))
            ->label([
                'Approved' => 'success',
                'Ongoing' => 'primary',
                'Billing' => 'warning',
                'Payment' => 'danger',
            ])
            ->sortable();
        /* $grid->column('invoice_processed', __('Invoice Status'))
            ->label([
                'Yes' => 'success',
                'No' => 'danger',
            ])
            ->display(function ($status) {
                $text = 'Yes';
                if ($status != 'Yes') {
                    $text = 'No';
                }
                if ($text == 'Yes') {
                    return "<span class='label label-success'>Genarated</span>";
                } else {
                    return "<span class='label label-danger'>Not Genarated</span>";
                }
            })->sortable(); */
        $grid->column('preview', __('Preview'))->display(function () {
            $link = url('medical-report?id=' . $this->id);
            return "<a href='$link' target='_blank'>Preview Report</a>";
        });

        $grid->column('actions', __('Actions'))
            ->display(function ($id) {
                return view('admin.actions', [
                    'id' => $this->id,
                    'endpoint' => 'consultations',
                    'hideActions' => true,
                    'links' => [
                        [
                            'label' => 'Consultation Report',
                            'icon' => 'eye',
                            'url' => url('consultations/' . $this->id),
                            'newTab' => true,
                        ],
                        [
                            'label' => 'Add payment record',
                            'icon' => 'plus',
                            'url' => admin_url('payment-records/create?id=' . $this->id)
                        ],
                        [
                            'label' => 'View invoice',
                            'icon' => 'file-pdf-o',
                            'url' => url('regenerate-invoice?id=' . $this->id),
                            'newTab' => true,
                        ],

                    ]
                ]);
            });

        $grid->disableActions();


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

        $url = $_SERVER['REQUEST_URI'];


        //method
        $method = $_SERVER['REQUEST_METHOD'];

        //check if is get 
        if (null && $method == 'GET') {
            $segments = explode('/', $url);
            //second last
            $id = $segments[count($segments) - 2];
            $item = Consultation::find($id);

            if ($item->main_status != 'Payment') {
                admin_error('This consultation is not ready for payment.');
                $form->disableSubmit();
                $form->disableViewCheck();
                $form->disableReset();
                return $form;
            }
            $item->process_invoice();
        }



        $form->divider('Consultation Details');
        $u = Admin::user();


        $form->display('patient_name', __('Patient'));
        $form->display('consultation_number', __('Consultation number'));
        $form->display('total_charges', 'Total Payable Amount (UGX)')->readonly();
        //total_paid
        $form->display('total_paid', 'Total Paid Amount (UGX)')->readonly();
        $form->divider();
        $form->display('total_due', 'Total Due Amount (UGX)')->readonly();
        $form->divider('Payment Records');

        $form->hasMany('payment_records', 'Click on (New) to add Payment Record', function (Form\NestedForm $form) {
            $form->currency('amount_paid', 'Amount Paid (UGX)')->symbol('UGX');
            $form->datetime('payment_date', 'Payment Date')->default(date('Y-m-d H:i:s'));
            $form->text('payment_method', 'Payment Method');
            $form->text('payment_reference', 'Payment Reference');
            $form->text('payment_remarks', 'Payment Remarks');
            $form->hidden('consultation_id');
        })->disableDelete();

        $form->disableCreatingCheck();
        $form->radio('main_status', __('Update Consultation Stage'))
            ->options([
                'Ongoing' => 'Ongoing',
                'Payment' => 'Ready for Payment',
            ]);
        return $form;
    }
}
