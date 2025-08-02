<?php

namespace App\Admin\Controllers;

use App\Models\Consultation;
use App\Models\Enterprise;
use App\Models\PaymentRecord;
use App\Models\User;
use App\Models\Utils;
use App\Traits\EnterpriseScopeTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        $u = Admin::user();
        $ent = Enterprise::find($u->enterprise_id);
        if ($ent == null) {
            admin_error('No enterprise found. Please contact your system administrator.');
            return redirect(admin_url('/'));
        }

        $grid = new Grid(new PaymentRecord());
        $grid->disableBatchActions();

        $grid->model()
            ->with(['consultation.patient', 'cashReceiver', 'createdBy', 'card'])
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('payment_date', 'desc');

        // Payment Identification
        $grid->column('payment_number', __('Payment #'))
            ->display(function () {
                return 'PR' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
            })
            ->sortable()
            ->label('primary');

        $grid->column('consultation_id', __('Consultation'))
            ->display(function ($id) {
                if (!$this->consultation) {
                    return '<span class="label label-default">N/A</span>';
                }
                $number = $this->consultation->consultation_number;
                $url = admin_url('consultations/' . $this->consultation->id);
                return "<a href='{$url}'>{$number}</a>";
            })
            ->sortable();

        $grid->column('patient_info', __('Patient'))
            ->display(function () {
                if (!$this->consultation || !$this->consultation->patient) {
                    return '<span class="label label-default">N/A</span>';
                }
                $patient = $this->consultation->patient;
                $url = admin_url('patients/' . $patient->id);
                return "<div class='patient-info'>
                            <strong><a href='{$url}'>{$patient->name}</a></strong><br>
                            <small class='text-muted'>{$patient->phone}</small>
                        </div>";
            });

        // Payment Details
        $grid->column('description', __('Description'))
            ->display(function ($description) {
                if (!$description) return '-';
                return strlen($description) > 50 ? 
                    substr($description, 0, 50) . '...' : 
                    $description;
            });

        $grid->column('payment_method', __('Method'))
            ->display(function ($method) {
                $badges = [
                    'Cash' => 'success',
                    'Card' => 'primary',
                    'Mobile Money' => 'warning',
                    'Flutterwave' => 'info',
                    'Bank Transfer' => 'default',
                ];
                $badge = $badges[$method] ?? 'default';
                return "<span class='label label-{$badge}'>{$method}</span>";
            })
            ->filter([
                'Cash' => 'Cash',
                'Card' => 'Card',
                'Mobile Money' => 'Mobile Money',
                'Flutterwave' => 'Flutterwave',
                'Bank Transfer' => 'Bank Transfer',
            ])
            ->sortable();

        $grid->column('payment_status', __('Status'))
            ->display(function ($status) {
                $badges = [
                    'Pending' => 'warning',
                    'Success' => 'success',
                    'Failed' => 'danger',
                    'Cancelled' => 'default',
                    'Refunded' => 'info',
                ];
                $badge = $badges[$status] ?? 'default';
                return "<span class='label label-{$badge}'>{$status}</span>";
            })
            ->filter([
                'Pending' => 'Pending',
                'Success' => 'Success',
                'Failed' => 'Failed',
                'Cancelled' => 'Cancelled',
                'Refunded' => 'Refunded',
            ])
            ->sortable();

        // Financial Information
        $grid->column('amount_payable', __('Payable'))
            ->display(function ($amount) {
                return 'UGX ' . number_format($amount, 0);
            })
            ->sortable();

        $grid->column('amount_paid', __('Paid'))
            ->display(function ($amount) {
                return '<strong>UGX ' . number_format($amount, 0) . '</strong>';
            })
            ->sortable();

        $grid->column('balance', __('Balance'))
            ->display(function ($balance) {
                if ($balance > 0) {
                    return "<span class='text-warning'><strong>UGX " . number_format($balance, 0) . "</strong></span>";
                } elseif ($balance < 0) {
                    return "<span class='text-info'><strong>UGX " . number_format(abs($balance), 0) . " (Overpaid)</strong></span>";
                } else {
                    return "<span class='text-success'><strong>Fully Paid</strong></span>";
                }
            })
            ->sortable();

        // Payment Reference & Contact
        $grid->column('payment_reference', __('Reference'))
            ->display(function ($ref) {
                return $ref ?: '-';
            });

        $grid->column('payment_phone_number', __('Phone'))
            ->display(function ($phone) {
                return $phone ?: '-';
            });

        // Staff Information
        $grid->column('cash_received_by_id', __('Received By'))
            ->display(function ($id) {
                if ($this->payment_method !== 'Cash' || !$this->cashReceiver) {
                    return '-';
                }
                return $this->cashReceiver->name;
            });

        $grid->column('created_by_id', __('Created By'))
            ->display(function ($id) {
                if (!$this->createdBy) {
                    return '-';
                }
                return $this->createdBy->name;
            });

        // Date & Time
        $grid->column('payment_date', __('Payment Date'))
            ->display(function ($date) {
                return Carbon::parse($date)->format('M d, Y H:i');
            })
            ->sortable();

        $grid->column('time_ago', __('Time Ago'))
            ->display(function () {
                return $this->payment_date ? 
                    Carbon::parse($this->payment_date)->diffForHumans() : 
                    '-';
            });

        $grid->column('actions', __('Actions'))
            ->display(function () {
                $editLink = admin_url('payment-records/' . $this->id . '/edit');
                $viewLink = admin_url('payment-records/' . $this->id);
                $receiptLink = url('payment-receipt?id=' . $this->id);
                
                return "
                    <div class='btn-group btn-group-xs'>
                        <a href='{$viewLink}' class='btn btn-xs btn-primary' title='View'>
                            <i class='fa fa-eye'></i>
                        </a>
                        <a href='{$editLink}' class='btn btn-xs btn-warning' title='Edit'>
                            <i class='fa fa-edit'></i>
                        </a>
                        <a href='{$receiptLink}' target='_blank' class='btn btn-xs btn-success' title='Receipt'>
                            <i class='fa fa-file-pdf-o'></i>
                        </a>
                    </div>
                ";
            });

        // Grid Filters
        $grid->filter(function($filter) use ($u) {
            $filter->disableIdFilter();
            
            $filter->like('consultation.consultation_number', 'Consultation Number');
            $filter->like('consultation.patient.name', 'Patient Name');
            $filter->like('consultation.patient.phone', 'Patient Phone');
            
            $filter->equal('payment_method', 'Payment Method')->select([
                'Cash' => 'Cash',
                'Card' => 'Card',
                'Mobile Money' => 'Mobile Money',
                'Flutterwave' => 'Flutterwave',
                'Bank Transfer' => 'Bank Transfer',
            ]);
            
            $filter->equal('payment_status', 'Payment Status')->select([
                'Pending' => 'Pending',
                'Success' => 'Success',
                'Failed' => 'Failed',
                'Cancelled' => 'Cancelled',
                'Refunded' => 'Refunded',
            ]);
            
            $filter->like('payment_reference', 'Payment Reference');
            $filter->like('payment_phone_number', 'Phone Number');
            
            $filter->between('amount_paid', 'Amount Range');
            $filter->between('payment_date', 'Payment Date')->datetime();
            
            // Balance filters
            $filter->where(function ($query) {
                $query->where('balance', '>', 0);
            }, 'Partial Payments', 'partial');
            
            $filter->where(function ($query) {
                $query->where('balance', '<=', 0);
            }, 'Full Payments', 'full');
            
            $filter->equal('cash_received_by_id', 'Received By')->select(
                User::where('enterprise_id', $u->enterprise_id)
                    ->whereIn('role', ['Receptionist', 'Admin', 'Cashier'])
                    ->pluck('name', 'id')
            );
        });

        // Grid Tools
        $grid->tools(function ($tools) {
            $tools->append('<div class="btn-group pull-right" style="margin-left: 10px;">
                <a href="' . admin_url('payment-records/export') . '" class="btn btn-sm btn-success">
                    <i class="fa fa-download"></i> Export
                </a>
                <a href="' . admin_url('payment-records/statistics') . '" class="btn btn-sm btn-info">
                    <i class="fa fa-bar-chart"></i> Statistics
                </a>
                <a href="' . admin_url('payment-records/reconciliation') . '" class="btn btn-sm btn-warning">
                    <i class="fa fa-balance-scale"></i> Reconciliation
                </a>
            </div>');
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
                ->options(Consultation::getPayableConsultations())
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
