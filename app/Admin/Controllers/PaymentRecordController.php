<?php

namespace App\Admin\Controllers;

use App\Models\Consultation;
use App\Models\PaymentRecord;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
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
        $grid->disableBatchActions();
        $grid->model()->orderBy('id', 'desc');
        $grid->quickSearch('description', 'payment_reference', 'payment_phone_number')->placeholder('Search by description, reference or phone number');


        $grid->column('created_at', __('Date'))
            ->display(function ($date) {
                return \App\Models\Utils::my_date_time($date);
            })->sortable();
        $grid->column('consultation_id', __('Consultation'))
            ->display(function ($id) {
                if ($this->consultation == null) {
                    return 'N/A';
                }
                return $this->consultation->name_text;
            })->sortable();
        $grid->column('description', __('Description'))->hide();
        $grid->column('amount_payable', __('Amount Payable'))->sortable();
        $grid->column('amount_paid', __('Amount Paid'))->sortable();
        $grid->column('balance', __('Balance'));
        $grid->column('payment_date', __('Payment Date'))
            ->sortable();
        $grid->column('payment_method', __('Payment Method'))
            ->label([
                'Cash' => 'info',
                'Card' => 'success',
                'Mobile Money' => 'warning',
                'Flutterwave' => 'danger',
            ]);
        $grid->column('payment_reference', __('Payment reference'));
        $grid->column('payment_status', __('Status'))
            ->label([
                'Pending' => 'default',
                'Success' => 'success',
                'Failed' => 'danger',
            ]);
        $grid->column('payment_remarks', __('Payment remarks'))->sortable();
        $grid->column('payment_phone_number', __('Payment phone number'))->hide();



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

        $hasConsultation = false;
        $consultation = null;
        $id = null;
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $consultation = \App\Models\Consultation::find($id);
            if ($consultation != null) {
                $hasConsultation = true;
            }
        }

        //$form-> is creating?
        if (!$form->isCreating() && (!$hasConsultation)) {
            $method = $_SERVER['REQUEST_METHOD'];
            //check if is get 
            if ($method == 'GET') {
                $url = $_SERVER['REQUEST_URI'];
                $segments = explode('/', $url);
                //second last
                $id = $segments[count($segments) - 2];
                $item = PaymentRecord::find($id);
                if ($item != null && $item->consultation != null) {
                    $consultation = $item->consultation;
                    $hasConsultation = true;
                }
            }
        }

        if ($hasConsultation) {
            $form->display('name_text', 'Consultation')
                ->default($consultation->name_text);
            //hidden consultation id
            $form->hidden('consultation_id', 'Consultation ID')
                ->default($consultation->id)
                ->required();
            //payable amount
            $form->display('amount_payable', 'Amount Payable (UGX)')
                ->default(number_format($consultation->total_charges));
            //amount paid amount_paid
            $form->display('amount_paid', 'Amount Paid (UGX)')
                ->default(number_format($consultation->total_paid));
            $form->divider();
            $form->display('balance', 'Balance (UGX)')
                ->default(number_format($consultation->total_due));
            $form->divider();
        } else {
            $form->select('consultation_id', __('Consultation'))
                ->options(Consultation::get_payble_consultations())
                ->rules('required')
                ->required();
        }

        $form->decimal('amount_paid', __('Amount Paid'))->rules('required')->required();
        $form->radio('payment_method', __('Payment Method'))
            ->options([
                'Cash' => 'Cash',
                'Card' => 'Card',
                'Mobile Money' => 'Mobile Money',
                'Flutterwave' => 'Flutterwave',
            ])
            ->default('Mobile Money')
            ->required()
            ->rules('required')
            ->when('Mobile Money', function ($form) {
                $form->text('payment_phone_number', __('Payment Phone Number'))->rules('required');
                $form->text('payment_reference', __('Payment Reference number'));
            })
            ->when('Card', function ($form) {

                /* $consultation = null;
                $id = null;
                if (isset($_GET['id'])) {
                    $id = $_GET['id'];
                    $consultation = \App\Models\Consultation::find($id);
                    if ($consultation != null) {
                        $hasConsultation = true;
                    }
                }
                $card_id = null;
                if ($consultation != null) {
                    $card_id = $consultation->id;
                }
 */
                $ajax_url = url(
                    '/api/ajax-cards'
                );
                $form->select('card_id', "Select card")
                    ->options(function ($id) {
                        $a = User::find($id);
                        if ($a) {
                            return [$a->id => "#" . $a->id . " - " . $a->card_number];
                        }
                    })
                    ->ajax($ajax_url)->rules('required');
            })
            ->when('Cash', function ($form) {
                $form->text('cash_receipt_number', __('Cash Receipt Number'))->rules('required');
                //rceived by
                $form->hidden('cash_received_by_id', 'Cash Received By')
                    ->default(Admin::user()->id)
                    ->required();
                $form->display('cash_received_by', 'Cash Received By')
                    ->default(Admin::user()->name);
            });

        $form->divider();
        $form->datetime('payment_date', __('Payment Date'))->rules('required')->required()->default(date('Y-m-d H:i:s'));
        $form->text('payment_remarks', __('Payment Remarks'));
        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        /* 
            $table->foreignIdFor(User::class, 'cash_received_by_id')->nullable();
            $table->foreignIdFor(User::class, 'created_by_id')->nullable();
            $table->text('cash_receipt_number')->nullable(); 
            $table->foreignIdFor(Company::class, 'company_id')->nullable();
            $table->text('card_number')->nullable();
            $table->text('card_type')->nullable();

            $table->text('flutterwave_reference')->nullable();
            $table->text('flutterwave_payment_type')->nullable();
            $table->text('flutterwave_payment_status')->nullable();
            $table->text('flutterwave_payment_message')->nullable();
            $table->text('flutterwave_payment_code')->nullable();
            $table->text('flutterwave_payment_data')->nullable();
            $table->text('flutterwave_payment_link')->nullable();
            $table->text('flutterwave_payment_amount')->nullable();
            $table->text('flutterwave_payment_customer_name')->nullable();
            $table->text('flutterwave_payment_customer_id')->nullable();
            $table->text('flutterwave_payment_customer_email')->nullable();
            $table->text('flutterwave_payment_customer_phone_number')->nullable();
            $table->text('flutterwave_payment_customer_full_name')->nullable();
            $table->text('flutterwave_payment_customer_created_at')->nullable();
        */



        return $form;
    }
}
