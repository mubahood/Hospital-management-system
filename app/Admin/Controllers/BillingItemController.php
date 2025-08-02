<?php

namespace App\Admin\Controllers;

use App\Models\BillingItem;
use App\Models\Consultation;
use App\Models\Enterprise;
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

class BillingItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Billing Items';

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

        $grid = new Grid(new BillingItem());
        $grid->disableBatchActions();

        $grid->model()
            ->with(['consultation.patient', 'enterprise'])
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('created_at', 'desc');

        // Basic Information
        $grid->column('billing_number', __('Billing #'))
            ->display(function () {
                return 'BI' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
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

        $grid->column('type', __('Billing Type'))
            ->display(function ($type) {
                $types = BillingItem::getBillingTypes();
                $label = $types[$type] ?? ucfirst($type);
                $badges = [
                    'consultation' => 'primary',
                    'laboratory' => 'info',
                    'radiology' => 'primary',
                    'pharmacy' => 'success',
                    'procedure' => 'warning',
                    'surgery' => 'danger',
                    'admission' => 'important',
                    'bed_charge' => 'default',
                    'nursing' => 'default',
                    'physiotherapy' => 'warning',
                    'specialist' => 'danger',
                    'emergency' => 'important',
                    'ambulance' => 'inverse',
                    'other' => 'default'
                ];
                $badge = $badges[$type] ?? 'default';
                return "<span class='label label-{$badge}'>{$label}</span>";
            })
            ->filter(BillingItem::getBillingTypes())
            ->sortable();

        $grid->column('description', __('Description'))
            ->display(function ($description) {
                if (!$description) return '-';
                return strlen($description) > 60 ? 
                    substr($description, 0, 60) . '...' : 
                    $description;
            })
            ->editable('textarea');

        $grid->column('price', __('Amount'))
            ->display(function ($price) {
                $formatted = 'UGX ' . number_format($price, 0);
                if ($price > 1000000) {
                    return "<strong class='text-danger'>{$formatted}</strong>";
                } elseif ($price > 100000) {
                    return "<strong class='text-warning'>{$formatted}</strong>";
                } else {
                    return "<strong class='text-success'>{$formatted}</strong>";
                }
            })
            ->sortable();

        $grid->column('price_category', __('Price Category'))
            ->display(function () {
                if ($this->price > 1000000) {
                    return "<span class='label label-danger'>High Value</span>";
                } elseif ($this->price > 100000) {
                    return "<span class='label label-warning'>Medium Value</span>";
                } else {
                    return "<span class='label label-success'>Standard</span>";
                }
            });

        $grid->column('created_at', __('Created'))
            ->display(function ($date) {
                return Carbon::parse($date)->format('M d, Y H:i');
            })
            ->sortable();

        $grid->column('time_ago', __('Time Ago'))
            ->display(function () {
                return $this->created_at->diffForHumans();
            })
            ->sortable();

        $grid->column('actions', __('Actions'))
            ->display(function () {
                $editLink = admin_url('billing-items/' . $this->id . '/edit');
                $viewLink = admin_url('billing-items/' . $this->id);
                $billLink = url('regenerate-invoice?id=' . $this->consultation_id);
                
                return "
                    <div class='btn-group btn-group-xs'>
                        <a href='{$viewLink}' class='btn btn-xs btn-primary' title='View'>
                            <i class='fa fa-eye'></i>
                        </a>
                        <a href='{$editLink}' class='btn btn-xs btn-warning' title='Edit'>
                            <i class='fa fa-edit'></i>
                        </a>
                        <a href='{$billLink}' target='_blank' class='btn btn-xs btn-success' title='Invoice'>
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
            
            $filter->equal('type', 'Billing Type')->select(BillingItem::getBillingTypes());
            $filter->like('description', 'Description');
            
            $filter->between('price', 'Price Range');
            $filter->between('created_at', 'Created Date')->datetime();
            
            // Price category filter
            $filter->where(function ($query) {
                $query->where('price', '>', 1000000);
            }, 'High Value Items (>1M UGX)', 'high_value');
            
            $filter->where(function ($query) {
                $query->whereBetween('price', [100000, 1000000]);
            }, 'Medium Value Items (100K-1M UGX)', 'medium_value');
            
            $filter->where(function ($query) {
                $query->where('price', '<', 100000);
            }, 'Standard Items (<100K UGX)', 'standard');
        });

        // Grid Tools
        $grid->tools(function ($tools) {
            $tools->append('<div class="btn-group pull-right" style="margin-left: 10px;">
                <a href="' . admin_url('billing-items/export') . '" class="btn btn-sm btn-success">
                    <i class="fa fa-download"></i> Export
                </a>
                <a href="' . admin_url('billing-items/statistics') . '" class="btn btn-sm btn-info">
                    <i class="fa fa-bar-chart"></i> Statistics
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
        $show = new Show(BillingItem::findOrFail($id));

        $show->field('billing_number', __('Billing Number'))
            ->as(function () {
                return 'BI' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
            });

        $show->divider('Billing Information');
        
        $show->field('type', __('Billing Type'))
            ->as(function ($type) {
                $types = BillingItem::getBillingTypes();
                return $types[$type] ?? ucfirst($type);
            });
            
        $show->field('description', __('Description'));
        
        $show->field('price', __('Amount'))
            ->as(function ($price) {
                return 'UGX ' . number_format($price, 2);
            });

        $show->field('price_category', __('Price Category'))
            ->as(function () {
                if ($this->price > 1000000) {
                    return 'High Value (Above 1M UGX)';
                } elseif ($this->price > 100000) {
                    return 'Medium Value (100K - 1M UGX)';
                } else {
                    return 'Standard (Below 100K UGX)';
                }
            })
            ->label([
                'High Value (Above 1M UGX)' => 'danger',
                'Medium Value (100K - 1M UGX)' => 'warning',
                'Standard (Below 100K UGX)' => 'success',
            ]);

        $show->divider('Consultation & Patient');
        
        $show->field('consultation.consultation_number', __('Consultation Number'));
        $show->field('consultation.patient.name', __('Patient Name'));
        $show->field('consultation.patient.phone', __('Patient Phone'));
        $show->field('consultation.patient.email', __('Patient Email'));
        $show->field('consultation.main_status', __('Consultation Status'));

        $show->divider('Enterprise & Timestamps');
        
        $show->field('enterprise.name', __('Enterprise'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        $show->panel()
            ->tools(function ($tools) {
                $tools->append('<a class="btn btn-sm btn-success" href="' . url('regenerate-invoice?id=' . request()->route('billing_item')) . '" target="_blank">
                    <i class="fa fa-file-pdf-o"></i> View Invoice
                </a>');
            });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new BillingItem());
        $u = Admin::user();

        $form->tab('Basic Information', function ($form) use ($u) {
            if ($form->isEditing()) {
                $form->display('billing_number', __('Billing Number'))
                    ->with(function ($value) {
                        return 'BI' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
                    });
            }

            $form->select('consultation_id', __('Consultation'))
                ->options(Consultation::where('enterprise_id', $u->enterprise_id)
                    ->with('patient')
                    ->get()
                    ->mapWithKeys(function ($consultation) {
                        $patientName = $consultation->patient ? $consultation->patient->name : 'Unknown';
                        return [$consultation->id => $consultation->consultation_number . ' - ' . $patientName];
                    }))
                ->rules('required')
                ->help('Select the consultation this billing item belongs to');

            $form->select('type', __('Billing Type'))
                ->options(BillingItem::getBillingTypes())
                ->rules('required')
                ->help('Select the type of service or item being billed');

            $form->textarea('description', __('Description'))
                ->rules('required|max:500')
                ->rows(4)
                ->help('Detailed description of the service or item');

            $form->decimal('price', __('Amount (UGX)'))
                ->rules('required|min:0')
                ->help('Enter the amount in Ugandan Shillings');

            $form->hidden('enterprise_id')->default($u->enterprise_id);
        });

        $form->tab('Additional Information', function ($form) {
            if ($form->isEditing()) {
                $form->display('price_category', __('Price Category'))
                    ->with(function () {
                        if ($this->price > 1000000) {
                            return 'High Value (Above 1M UGX)';
                        } elseif ($this->price > 100000) {
                            return 'Medium Value (100K - 1M UGX)';
                        } else {
                            return 'Standard (Below 100K UGX)';
                        }
                    });

                $form->display('consultation.patient.name', __('Patient Name'));
                $form->display('consultation.patient.phone', __('Patient Contact'));
                $form->display('consultation.main_status', __('Consultation Status'));
            }

            $form->display('created_at', __('Created At'));
            $form->display('updated_at', __('Last Updated'));
        });

        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
        });

        $form->tools(function (Form\Tools $tools) {
            if (request()->route('billing_item')) {
                $tools->append('<a class="btn btn-success btn-sm" href="' . url('regenerate-invoice?id=' . request()->route('billing_item')) . '" target="_blank">
                    <i class="fa fa-file-pdf-o"></i> View Invoice
                </a>');
            }
        });

        // Auto-calculate total charges for consultation when saving
        $form->saved(function (Form $form) {
            $billingItem = $form->model();
            if ($billingItem->consultation) {
                $total = BillingItem::where('consultation_id', $billingItem->consultation_id)->sum('price');
                $billingItem->consultation->update(['total_charges' => $total]);
            }
        });

        return $form;
    }
}
