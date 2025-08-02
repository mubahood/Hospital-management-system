<?php

namespace App\Admin\Controllers;

use App\Models\Consultation;
use App\Models\Enterprise;
use App\Models\MedicalService;
use App\Models\Patient;
use App\Models\StockItem;
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

class MedicalServiceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Medical Services';

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

        $grid = new Grid(new MedicalService());
        $grid->disableBatchActions();
        $grid->disableCreateButton();

        // Filter by enterprise and active consultations
        $consultation_ids = Consultation::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $u->enterprise_id)
            ->where(function ($query) {
                $query->where('main_status', 'Ongoing')
                      ->orWhere('main_status', 'Billing');
            })
            ->pluck('id')->toArray();

        $grid->model()
            ->with(['consultation.patient', 'assigned_to', 'medical_service_items'])
            ->where('enterprise_id', $u->enterprise_id)
            ->whereIn('consultation_id', $consultation_ids)
            ->orderBy('created_at', 'desc');

        // Basic Information
        $grid->column('service_number', __('Service #'))
            ->display(function () {
                return 'MS' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
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

        $grid->column('type', __('Service Type'))
            ->display(function ($type) {
                $types = MedicalService::getServiceTypes();
                $label = $types[$type] ?? ucfirst($type);
                $badges = [
                    'laboratory' => 'info',
                    'radiology' => 'primary',
                    'pharmacy' => 'success',
                    'physiotherapy' => 'warning',
                    'nursing' => 'default',
                    'specialist' => 'danger',
                    'surgery' => 'important',
                    'procedure' => 'inverse',
                    'other' => 'default'
                ];
                $badge = $badges[$type] ?? 'default';
                return "<span class='label label-{$badge}'>{$label}</span>";
            })
            ->filter(MedicalService::getServiceTypes())
            ->sortable();

        $grid->column('assigned_to_id', __('Assigned To'))
            ->display(function ($id) {
                if (!$this->assigned_to) {
                    return '<span class="text-muted">Unassigned</span>';
                }
                $url = admin_url('users/' . $this->assigned_to->id);
                return "<a href='{$url}'>{$this->assigned_to->name}</a>";
            })
            ->sortable();

        $grid->column('status', __('Status'))
            ->display(function ($status) {
                $badges = [
                    'pending' => 'warning',
                    'in_progress' => 'info',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                ];
                $badge = $badges[$status] ?? 'default';
                $label = ucfirst(str_replace('_', ' ', $status));
                return "<span class='label label-{$badge}'>{$label}</span>";
            })
            ->filter([
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ])
            ->sortable();

        // Pricing Information
        $grid->column('unit_price', __('Unit Price'))
            ->display(function ($price) {
                return $price ? 'UGX ' . number_format($price, 0) : '-';
            })
            ->sortable();

        $grid->column('quantity', __('Qty'))
            ->display(function ($qty) {
                return $qty ?? 1;
            })
            ->sortable();

        $grid->column('total_price', __('Total Price'))
            ->display(function ($price) {
                return $price ? '<strong>UGX ' . number_format($price, 0) . '</strong>' : '-';
            })
            ->sortable();

        // Service Details
        $grid->column('instruction', __('Instructions'))
            ->display(function ($instruction) {
                if (!$instruction) return '-';
                return strlen($instruction) > 50 ? 
                    substr($instruction, 0, 50) . '...' : 
                    $instruction;
            })
            ->editable('textarea');

        $grid->column('specialist_outcome', __('Outcome'))
            ->display(function ($outcome) {
                if (!$outcome) {
                    return '<span class="text-muted">Pending</span>';
                }
                return strlen($outcome) > 50 ? 
                    substr($outcome, 0, 50) . '...' : 
                    $outcome;
            })
            ->editable('textarea');

        // Items Summary
        $grid->column('items_summary', __('Items'))
            ->display(function () {
                $count = $this->medical_service_items->count();
                if ($count == 0) {
                    return '<span class="text-muted">No items</span>';
                }
                $totalItems = $this->medical_service_items->sum('quantity');
                return "<span class='badge badge-info'>{$count} types, {$totalItems} items</span>";
            });

        $grid->column('created_at', __('Created'))
            ->display(function ($date) {
                return Carbon::parse($date)->format('M d, Y H:i');
            })
            ->sortable();

        $grid->column('actions', __('Actions'))
            ->display(function () {
                $reportLink = url('medical-report?id=' . $this->id);
                $editLink = admin_url('medical-services/' . $this->id . '/edit');
                $viewLink = admin_url('medical-services/' . $this->id);
                
                return "
                    <div class='btn-group btn-group-xs'>
                        <a href='{$viewLink}' class='btn btn-xs btn-primary' title='View'>
                            <i class='fa fa-eye'></i>
                        </a>
                        <a href='{$editLink}' class='btn btn-xs btn-warning' title='Edit'>
                            <i class='fa fa-edit'></i>
                        </a>
                        <a href='{$reportLink}' target='_blank' class='btn btn-xs btn-success' title='Report'>
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
            
            $filter->equal('type', 'Service Type')->select(MedicalService::getServiceTypes());
            $filter->equal('status', 'Status')->select(MedicalService::getStatuses());
            
            $filter->equal('assigned_to_id', 'Assigned To')->select(
                User::where('enterprise_id', $u->enterprise_id)
                    ->whereIn('role', ['Doctor', 'Specialist', 'Nurse'])
                    ->pluck('name', 'id')
            );
            
            $filter->between('created_at', 'Created Date')->datetime();
            $filter->between('total_price', 'Price Range');
        });

        // Grid Tools
        $grid->tools(function ($tools) {
            $tools->append('<div class="btn-group pull-right" style="margin-left: 10px;">
                <a href="' . admin_url('medical-services/export') . '" class="btn btn-sm btn-success">
                    <i class="fa fa-download"></i> Export
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
        $show = new Show(MedicalService::findOrFail($id));

        $show->field('service_number', __('Service Number'))
            ->as(function () {
                return 'MS' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
            });

        $show->divider('Service Information');
        
        $show->field('type', __('Service Type'))
            ->as(function ($type) {
                $types = MedicalService::getServiceTypes();
                return $types[$type] ?? ucfirst($type);
            });
            
        $show->field('status', __('Status'))
            ->as(function ($status) {
                return ucfirst(str_replace('_', ' ', $status));
            })
            ->label([
                'pending' => 'warning',
                'in_progress' => 'info', 
                'completed' => 'success',
                'cancelled' => 'danger',
            ]);

        $show->field('description', __('Description'));
        $show->field('instruction', __('Instructions'));
        $show->field('specialist_outcome', __('Specialist Outcome'));
        $show->field('remarks', __('Remarks'));

        $show->divider('Consultation & Patient');
        
        $show->field('consultation.consultation_number', __('Consultation Number'));
        $show->field('consultation.patient.name', __('Patient Name'));
        $show->field('consultation.patient.phone', __('Patient Phone'));
        $show->field('consultation.patient.email', __('Patient Email'));

        $show->divider('Assignment & Pricing');
        
        $show->field('assigned_to.name', __('Assigned To'));
        $show->field('receptionist.name', __('Receptionist'));
        
        $show->field('unit_price', __('Unit Price'))
            ->as(function ($price) {
                return $price ? 'UGX ' . number_format($price, 2) : 'Not set';
            });
            
        $show->field('quantity', __('Quantity'));
        
        $show->field('total_price', __('Total Price'))
            ->as(function ($price) {
                return $price ? 'UGX ' . number_format($price, 2) : 'Not calculated';
            });

        $show->divider('Service Items');
        
        $show->medical_service_items('Medical Service Items', function ($items) {
            $items->disableCreateButton();
            $items->disableActions();
            
            $items->stock_item_id('Stock Item')->as(function ($id) {
                $item = \App\Models\StockItem::find($id);
                return $item ? $item->name : 'N/A';
            });
            $items->quantity('Quantity');
            $items->description('Description');
            $items->total_price('Total Price')->as(function ($price) {
                return 'UGX ' . number_format($price, 2);
            });
        });

        $show->divider('Timestamps');
        
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        $show->panel()
            ->tools(function ($tools) {
                $tools->append('<a class="btn btn-sm btn-success" href="' . url('medical-report?id=' . request()->route('medical_service')) . '" target="_blank">
                    <i class="fa fa-file-pdf-o"></i> View Report
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
        $form = new Form(new MedicalService());
        $u = Admin::user();
        
        // Disable creation - services are created from consultations
        if ($form->isCreating()) {
            admin_error('Medical Services cannot be created directly. They are created from Consultations.');
            $form->disableSubmit();
            $form->disableViewCheck();
            $form->disableReset();
            return $form;
        }

        $form->tab('Basic Information', function ($form) use ($u) {
            $form->display('service_number', __('Service Number'))
                ->with(function ($value) {
                    return 'MS' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
                });

            $form->display('type', __('Service Type'))
                ->with(function ($value) {
                    $types = MedicalService::getServiceTypes();
                    return $types[$value] ?? ucfirst($value);
                });

            $form->display('consultation.consultation_number', __('Consultation'));
            $form->display('consultation.patient.name', __('Patient'));

            $form->select('assigned_to_id', __('Assign To'))
                ->options(User::where('enterprise_id', $u->enterprise_id)
                    ->whereIn('role', ['Doctor', 'Specialist', 'Nurse'])
                    ->pluck('name', 'id'))
                ->help('Select a medical professional to handle this service');

            $form->textarea('description', __('Service Description'))
                ->rows(3)
                ->help('Detailed description of the medical service');

            $form->textarea('instruction', __('Instructions'))
                ->rows(4)
                ->help('Specific instructions for the assigned medical professional');

            $form->textarea('specialist_outcome', __('Specialist Outcome'))
                ->rows(4)
                ->help('Results, findings, or outcomes from the medical service');

            $form->textarea('remarks', __('Additional Remarks'))
                ->rows(3)
                ->help('Any additional notes or comments');
        });

        $form->tab('Pricing & Billing', function ($form) {
            $form->decimal('unit_price', __('Unit Price'))
                ->help('Price per unit of service in UGX');
                
            $form->number('quantity', __('Quantity'))
                ->default(1)
                ->min(1)
                ->help('Number of units for this service');

            $form->decimal('total_price', __('Total Price'))
                ->help('Total price will be calculated automatically based on unit price and quantity');

            $form->saving(function (Form $form) {
                // Auto-calculate total price
                if ($form->unit_price && $form->quantity) {
                    $form->total_price = $form->unit_price * $form->quantity;
                }
            });
        });

        $form->tab('Service Items', function ($form) {
            $form->hasMany('medicalServiceItems', 'Medical Service Items', function (Form\NestedForm $form) {
                $form->select('stock_item_id', __('Stock Item'))
                    ->options(StockItem::getDropdownOptions())
                    ->rules('required')
                    ->help('Select the medical item or supply');

                $form->decimal('quantity', __('Quantity'))
                    ->rules('required|min:0.01')
                    ->help('Quantity used');

                $form->text('description', __('Description'))
                    ->rules('required')
                    ->help('Description of how the item was used');

                $form->decimal('unit_price', __('Unit Price'))
                    ->help('Price per unit (optional)');

                $form->decimal('total_price', __('Total Price'))
                    ->help('Total price for this item');

                $form->file('file', __('Attachment'))
                    ->uniqueName()
                    ->removable()
                    ->help('Upload any relevant documents or images');

                $form->text('remarks', __('Remarks'))
                    ->help('Additional notes for this item');
            });
        });

        $form->tab('Status & Workflow', function ($form) {
            $form->radio('status', __('Service Status'))
                ->options(MedicalService::getStatuses())
                ->rules('required')
                ->help('Update the current status of the medical service')
                ->when('completed', function (Form $form) {
                    $form->textarea('completion_notes', __('Completion Notes'))
                        ->help('Add any final notes upon completion');
                })
                ->when('cancelled', function (Form $form) {
                    $form->textarea('cancellation_reason', __('Cancellation Reason'))
                        ->rules('required')
                        ->help('Explain why this service was cancelled');
                });

            $form->file('file', __('Service Report/File'))
                ->uniqueName()
                ->removable()
                ->help('Upload any reports, results, or documentation');

            $form->display('created_at', __('Created At'));
            $form->display('updated_at', __('Last Updated'));
        });

        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->append('<a class="btn btn-success btn-sm" href="' . url('medical-report?id=' . request()->route('medical_service')) . '" target="_blank">
                <i class="fa fa-file-pdf-o"></i> View Report
            </a>');
        });

        return $form;
    }
}
