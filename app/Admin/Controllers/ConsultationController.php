<?php

namespace App\Admin\Controllers;

use App\Models\Consultation;
use App\Models\Service;
use App\Models\User;
use App\Models\Utils;
use App\Models\Enterprise;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Carbon\Carbon;

class ConsultationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Consultation Management';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Consultation());
        
        // Add enterprise scoping
        $user = Admin::user();
        if ($user && $user->enterprise_id) {
            $grid->model()->where('enterprise_id', $user->enterprise_id);
        }
        
        $grid->disableBatchActions();
        $grid->model()->where([
            'main_status' => 'Pending',
        ])->orderBy('id', 'desc');
        $grid->quickSearch('patient_name', 'patient_contact')->placeholder('Search by name or contact');
        $grid->column('id', __('Id'))->sortable()->hide();
        $grid->column('consultation_number', __('Consultation number'))
            ->sortable();
        $grid->column('created_at', __('Date'))
            ->display(function ($date) {
                return Utils::my_date_time($date);
            })->sortable();
        $grid->column('updated_at', __('Updated'))
            ->display(function ($date) {
                return Utils::my_date_time($date);
            })->sortable()
            ->hide();
        $grid->column('patient_id', __('Patient'))
            ->display(function ($patient_id) {
                if (!$patient_id) {
                    return 'N/A';
                }
                $patient = User::withoutGlobalScope('enterprise')->find($patient_id);
                if ($patient == null) {
                    return 'N/A';
                }
                return $patient->name;
            })->sortable();
        $grid->column('receptionist_id', __('Receptionist'))
            ->display(function ($receptionist_id) {
                if (!$receptionist_id) {
                    return 'N/A';
                }
                $receptionist = User::withoutGlobalScope('enterprise')->find($receptionist_id);
                if ($receptionist == null) {
                    return 'N/A';
                }
                return $receptionist->name;
            })->sortable()->hide();;


        $grid->column('patient_contact', __('Contact'));
        $grid->column('appointment_date', __('Appointment Date'))
            ->display(function ($date) {
                if (!$date) return 'N/A';
                return Utils::my_date_time($date);
            })->sortable();
        $grid->column('doctor_id', __('Doctor'))
            ->display(function ($doctor_id) {
                if (!$doctor_id) {
                    return 'N/A';
                }
                $doctor = User::withoutGlobalScope('enterprise')->find($doctor_id);
                if ($doctor == null) {
                    return 'N/A';
                }
                return "Dr. " . $doctor->name;
            })->sortable();
        $grid->column('appointment_status', __('Status'))
            ->display(function ($status) {
                $colors = [
                    'scheduled' => 'primary',
                    'confirmed' => 'success',
                    'cancelled' => 'danger',
                    'completed' => 'info',
                    'no-show' => 'warning'
                ];
                $color = $colors[$status] ?? 'secondary';
                return "<span class='badge badge-{$color}'>" . ucfirst($status) . "</span>";
            });
        $grid->column('appointment_type', __('Type'))
            ->display(function ($type) {
                return ucfirst(str_replace('_', ' ', $type ?? 'consultation'));
            });
        $grid->column('contact_address', __('Address'))->sortable()->hide();
        $grid->column('preferred_date_and_time', __('Preferred Date'))
            ->sortable()
            ->hide();
        $grid->column('services_requested', __('Services Requested'))
            ->sortable();
        $grid->column('reason_for_consultation', __('Reason for consultation'))
            ->sortable()
            ->hide();
        // $grid->column('request_status', __('Request status'));
        // $grid->column('request_date', __('Request Date'));
        // $grid->column('request_remarks', __('Request Remarks'));
        // $grid->column('receptionist_comment', __('Receptionist comment'));
        $grid->column('temperature', __('Temperature'))->sortable();
        $grid->column('weight', __('Weight'))->sortable();
        $grid->column('height', __('Height'))->sortable()->hide();
        $grid->column('bmi', __('Bmi'))->sortable()->hide();
        $grid->column('preview', __('Preview'))->display(function () {
            $link = url('medical-report?id=' . $this->getKey());
            return "<a href='$link' target='_blank'>Preview Report</a>";
        });

        $grid->column('main_status', __('Status'))
            ->filter([
                'Pending' => 'Pending',
                'Completed' => 'Completed',
                'Ongoing' => 'Ongoing',
                'Rejected' => 'Rejected',
                'Cancelled' => 'Cancelled',
                'Approved' => 'Approved',
            ])->label([
                'Pending' => 'primary',
                'Approved' => 'success',
                'Completed' => 'success',
                'Ongoing' => 'success',
                'Rejected' => 'danger',
                'Rescheduled' => 'warning',
            ])->sortable();

        // Add appointment actions for records with appointment_date
        $grid->actions(function ($actions) {
            $row = $actions->row;
            if ($row->appointment_date) {
                $actions->add(new \App\Admin\Actions\ConfirmAppointment);
                $actions->add(new \App\Admin\Actions\CancelAppointment);
            }
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
        
        $show->divider('Appointment Information');
        $show->field('doctor_id', __('Assigned Doctor'))->as(function ($doctor_id) {
            if (!$doctor_id) return 'N/A';
            $doctor = User::withoutGlobalScope('enterprise')->find($doctor_id);
            return $doctor ? "Dr. " . $doctor->name : 'N/A';
        });
        $show->field('appointment_date', __('Appointment Date & Time'));
        $show->field('appointment_type', __('Appointment Type'))->as(function ($type) {
            return ucfirst(str_replace('_', ' ', $type ?? 'consultation'));
        });
        $show->field('appointment_priority', __('Priority'))->as(function ($priority) {
            return ucfirst($priority ?? 'normal');
        });
        $show->field('appointment_status', __('Status'))->as(function ($status) {
            return ucfirst($status ?? 'scheduled');
        });
        $show->field('duration_minutes', __('Duration (minutes)'));
        $show->field('is_recurring', __('Recurring'))->as(function ($recurring) {
            return $recurring ? 'Yes' : 'No';
        });
        $show->field('recurrence_pattern', __('Recurrence Pattern'))->as(function ($pattern) {
            return $pattern ? ucfirst($pattern) : 'N/A';
        });
        $show->field('appointment_notes', __('Appointment Notes'));
        
        $show->divider('Health Information');
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

        $u = Admin::user();
        $ent = Enterprise::find($u->enterprise_id);
        if ($ent == null) {
            admin_error('No enterprise found. Please contact your system administrator.');
            return redirect(admin_url('/'));
        }

        // Hidden enterprise_id field
        $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
            ->value($u->enterprise_id);

        $form->divider('Consultation Details');

        $u = Admin::user();

        $ajax_url = url(
            '/api/ajax?'
                . "search_by_1=name"
                . "&search_by_2=id"
                . "&model=User"
                // . "&query_user_type=Patient"
        );
        $form->select('patient_id', "Select Patient")
            ->options(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
            ->ajax($ajax_url)->rules('required');

        $form->hidden('receptionist_id', __('Receptionist id'))->default($u->id);
        $form->tags('services_requested', __('Services Requested'))
            ->options(Service::getList())
            ->rules('required');
        $form->quill('reason_for_consultation', __('Consultation Details'))->rules('required');

        $form->divider('Appointment Scheduling');
        
        // Doctor selection for appointments
        $doctorOptions = User::withoutGlobalScope('enterprise')
            ->where('user_type', 'Doctor')
            ->where('enterprise_id', $u->enterprise_id)
            ->get()
            ->mapWithKeys(function ($doctor) {
                return [$doctor->id => "Dr. {$doctor->name} - {$doctor->title}"];
            });
        
        $form->select('doctor_id', __('Assigned Doctor'))
            ->options($doctorOptions)
            ->help('Select the doctor for this consultation/appointment');
            
        $form->datetime('appointment_date', __('Appointment Date & Time'))
            ->help('Set specific appointment date and time');
            
        $form->select('appointment_type', __('Appointment Type'))
            ->options([
                'consultation' => 'Regular Consultation',
                'follow_up' => 'Follow-up',
                'check_up' => 'Check-up',
                'procedure' => 'Medical Procedure',
                'emergency' => 'Emergency'
            ])
            ->default('consultation');
            
        $form->select('appointment_priority', __('Priority'))
            ->options([
                'low' => 'Low Priority',
                'normal' => 'Normal Priority',
                'high' => 'High Priority',
                'urgent' => 'Urgent'
            ])
            ->default('normal');
            
        $form->number('duration_minutes', __('Duration (minutes)'))
            ->default(30)
            ->min(15)
            ->max(240)
            ->help('Appointment duration in minutes');
            
        $form->select('appointment_status', __('Appointment Status'))
            ->options([
                'scheduled' => 'Scheduled',
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled',
                'completed' => 'Completed',
                'no-show' => 'No Show'
            ])
            ->default('scheduled');
            
        // Recurring appointments section
        $form->switch('is_recurring', __('Recurring Appointment'))
            ->help('Enable if this is a recurring appointment series');
            
        $form->select('recurrence_pattern', __('Recurrence Pattern'))
            ->options([
                'daily' => 'Daily',
                'weekly' => 'Weekly',
                'bi_weekly' => 'Bi-weekly',
                'monthly' => 'Monthly',
                'yearly' => 'Yearly'
            ])
            ->when('!=', '', function (Form $form) {
                $form->number('recurrence_interval', __('Repeat Every'))
                    ->default(1)
                    ->min(1)
                    ->help('Repeat every X intervals (e.g., every 2 weeks)');
                    
                $form->date('recurrence_end_date', __('End Date'))
                    ->help('When to stop creating recurring appointments');
            });
            
        $form->textarea('appointment_notes', __('Appointment Notes'))
            ->help('Special notes or instructions for this appointment');

        $form->divider('Initial Health Check Details');
        $form->decimal('temperature', __('Temperature'));
        $form->decimal('weight', __('Weight'));
        $form->decimal('height', __('Height'));
        $form->decimal('bmi', __('BMI'));

        $form->radio('main_status', __('Consultation status'))
            ->options([
                'Pending' => 'Pending',
                'Approved' => 'Approved',
                'Ongoing' => 'Ongoing',
                'Rejected' => 'Rejected',
                'Cancelled' => 'Cancelled',
            ])
            ->default('Pending')
            ->when('Ongoing', function ($form) {
                $form->text('consultation_number', __('Consultation number'))->default(Consultation::generate_consultation_number());
                $form->text('receptionist_comment', __('Receptionist Remarks'));
            })
            ->when('Approved', function ($form) {
                $form->datetime('request_date', __('Scheduled Date and Time'))->rules('required|date_format:Y-m-d H:i:s|after:now');
                $form->text('request_remarks', __('Message to the applicant'))->rules('required');
            })->when('Rejected', function ($form) {
                $form->text('request_remarks', __('Message to the applicant'))->rules('required');
            })->rules('required')
            ->when('in', [ 'Ongoing'], function ($form) {
                $form->divider('Medical Services');
                $u = Admin::user();
                //hidden receptionist_id
                $form->hidden('receptionist_id', __('Receptionist id'))->default($u->id);
                $form->hasMany('medicalServices', __('Press the "New" button to add a Medical Services'), function (Form\NestedForm $form) {
                    $form->select('type', __('Select Service'))
                        ->options(Service::all()->pluck('name', 'name')->toArray())
                        ->rules('required');
                    // assigned_to_id assign to specialist or doctor
                    $form->select('assigned_to_id', __('Assigned To'))
                        ->options(User::getDoctors())
                        ->rules('required');
                    //status hide as hidden
                    $form->hidden('status', __('Status'))->default('Pending');
                    //instruction
                    $form->text('instruction', __('Instructions'));
                });
            });

        $form->saving(function (Form $form) {
            $u = Admin::user();
            
            // Set enterprise_id to current user's enterprise
            if (!$form->enterprise_id) {
                $form->enterprise_id = $u->enterprise_id ?? 1;
            }
            
            // Auto-calculate BMI if height and weight are provided
            if ($form->height && $form->weight) {
                $heightInMeters = $form->height / 100;
                $form->bmi = round($form->weight / ($heightInMeters * $heightInMeters), 2);
            }
            
            // Generate consultation number for ongoing status
            if ($form->main_status === 'Ongoing' && !$form->consultation_number) {
                $form->consultation_number = Consultation::generate_consultation_number();
            }
            
            // Handle appointment scheduling
            if ($form->appointment_date && $form->doctor_id) {
                // Auto-set appointment end time based on duration
                if ($form->duration_minutes) {
                    $startTime = Carbon::parse($form->appointment_date);
                    $form->appointment_start_time = $startTime->format('H:i:s');
                    $form->appointment_end_time = $startTime->addMinutes($form->duration_minutes)->format('H:i:s');
                }
                
                // Set default appointment status if not set
                if (!$form->appointment_status) {
                    $form->appointment_status = 'scheduled';
                }
            }
            
            // Handle status transitions for appointments
            if ($form->appointment_status === 'confirmed' && !$form->confirmed_at) {
                $form->confirmed_at = now();
                $form->confirmed_by = $u->id;
            }
            
            if ($form->appointment_status === 'cancelled' && !$form->cancelled_at) {
                $form->cancelled_at = now();
                $form->cancelled_by = $u->id;
            }
        });
        
        $form->saved(function (Form $form) {
            $consultation = $form->model();
            
            // Create recurring appointments if enabled
            if ($consultation->is_recurring && 
                $consultation->recurrence_pattern && 
                $consultation->appointment_date) {
                
                try {
                    $consultation->createRecurringAppointments([
                        'pattern' => $consultation->recurrence_pattern,
                        'interval' => $consultation->recurrence_interval ?? 1,
                        'end_date' => $consultation->recurrence_end_date,
                        'max_occurrences' => 50 // Safety limit
                    ]);
                    
                    admin_success('Recurring appointments created successfully!');
                } catch (\Exception $e) {
                    admin_error('Error creating recurring appointments: ' . $e->getMessage());
                }
            }
        });

        return $form;
    }

    /**
     * Display calendar view
     */
    public function calendar()
    {
        $user = Admin::user();
        
        // Get doctors for filter dropdown
        $doctors = User::withoutGlobalScope('enterprise')
            ->where('user_type', 'Doctor')
            ->where('enterprise_id', $user->enterprise_id)
            ->select('id', 'name')
            ->get();

        return view('admin.appointments.calendar', compact('doctors'));
    }

    /**
     * Get appointments for calendar API
     */
    public function calendarApi()
    {
        $user = Admin::user();
        
        $query = Consultation::where('enterprise_id', $user->enterprise_id)
            ->whereNotNull('appointment_date');

        // Apply filters
        if (request('doctor_id')) {
            $query->where('doctor_id', request('doctor_id'));
        }
        
        if (request('status')) {
            $query->where('appointment_status', request('status'));
        }
        
        if (request('type')) {
            $query->where('appointment_type', request('type'));
        }

        // Date range filter
        if (request('start') && request('end')) {
            $query->whereBetween('appointment_date', [
                request('start'),
                request('end')
            ]);
        }

        $appointments = $query->with(['doctor'])->get();

        $events = $appointments->map(function ($appointment) {
            $doctor = $appointment->doctor;
            $doctorName = $doctor ? $doctor->name : 'Unknown';
            
            $startTime = $appointment->appointment_date;
            $endTime = $appointment->appointment_date;
            
            // Calculate end time if duration is set
            if ($appointment->duration_minutes) {
                $endTime = Carbon::parse($appointment->appointment_date)
                    ->addMinutes($appointment->duration_minutes);
            }

            return [
                'id' => $appointment->id,
                'title' => $appointment->patient_name . ' - Dr. ' . $doctorName,
                'start' => $startTime,
                'end' => $endTime,
                'status' => $appointment->appointment_status ?? 'scheduled',
                'priority' => $appointment->appointment_priority ?? 'normal',
                'patient_name' => $appointment->patient_name,
                'patient_contact' => $appointment->patient_contact,
                'contact_address' => $appointment->contact_address,
                'doctor_name' => $doctorName,
                'type' => ucfirst(str_replace('_', ' ', $appointment->appointment_type ?? 'consultation')),
                'reason_for_consultation' => $appointment->reason_for_consultation,
                'appointment_notes' => $appointment->appointment_notes,
                'tooltip' => $appointment->patient_name . ' - ' . 
                           Carbon::parse($startTime)->format('H:i') . 
                           ($appointment->appointment_type ? ' (' . ucfirst($appointment->appointment_type) . ')' : '')
            ];
        });

        return response()->json($events);
    }

    /**
     * Update appointment status via API
     */
    public function updateStatus($id)
    {
        $user = Admin::user();
        
        try {
            $consultation = Consultation::where('enterprise_id', $user->enterprise_id)
                ->findOrFail($id);
            
            $newStatus = request('status');
            $oldStatus = $consultation->appointment_status;
            
            $consultation->appointment_status = $newStatus;
            
            // Set timestamps based on status
            if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed') {
                $consultation->confirmed_at = now();
                $consultation->confirmed_by = $user->id;
            }
            
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $consultation->cancelled_at = now();
                $consultation->cancelled_by = $user->id;
            }
            
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $consultation->completed_at = now();
            }
            
            $consultation->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment status updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating appointment status: ' . $e->getMessage()
            ], 500);
        }
    }
}
