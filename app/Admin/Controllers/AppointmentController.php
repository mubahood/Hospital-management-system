<?php

namespace App\Admin\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Department;
use App\Models\Room;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Appointments';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Appointment());

        $grid->model()->with(['patient', 'doctor', 'department', 'room'])
                     ->orderBy('appointment_date', 'desc');

        // Quick search
        $grid->quickSearch(['appointment_number', 'patient.name', 'doctor.name', 'reason'])
             ->placeholder('Search by appointment #, patient, doctor, or reason');

        // Columns
        $grid->column('appointment_number', __('Appointment #'))
             ->sortable()
             ->copyable();

        $grid->column('appointment_date', __('Date & Time'))
             ->display(function ($date) {
                 return Utils::my_date_time($date);
             })
             ->sortable();

        $grid->column('patient.name', __('Patient'))
             ->display(function ($name) {
                 if ($this->patient) {
                     return "<strong>{$name}</strong><br><small>{$this->patient->phone}</small>";
                 }
                 return $name;
             })
             ->sortable();

        $grid->column('doctor.name', __('Doctor'))
             ->display(function ($name) {
                 if ($this->doctor) {
                     $deptName = ($this->department && $this->department->name) ? $this->department->name : 'N/A';
                     return "<strong>{$name}</strong><br><small>{$deptName}</small>";
                 }
                 return $name;
             })
             ->sortable();

        $grid->column('appointment_type', __('Type'))
             ->using(Appointment::getAppointmentTypes())
             ->label([
                 'consultation' => 'primary',
                 'follow_up' => 'info',
                 'surgery' => 'danger',
                 'procedure' => 'warning',
                 'lab_test' => 'success',
                 'imaging' => 'info',
                 'therapy' => 'primary',
                 'vaccination' => 'success',
                 'emergency' => 'danger'
             ])
             ->sortable();

        $grid->column('status', __('Status'))
             ->using(Appointment::getStatusOptions())
             ->dot([
                 'scheduled' => 'info',
                 'confirmed' => 'primary',
                 'in_progress' => 'warning',
                 'completed' => 'success',
                 'cancelled' => 'danger',
                 'no_show' => 'secondary',
                 'rescheduled' => 'warning'
             ])
             ->sortable();

        $grid->column('priority', __('Priority'))
             ->using(Appointment::getPriorityLevels())
             ->label([
                 'low' => 'secondary',
                 'normal' => 'primary',
                 'high' => 'warning',
                 'urgent' => 'danger'
             ])
             ->sortable();

        $grid->column('duration_minutes', __('Duration'))
             ->display(function ($minutes) {
                 return $minutes . ' min';
             })
             ->sortable();

        $grid->column('room.name', __('Room'))
             ->display(function ($name) {
                 return $name ?: 'Not assigned';
             })
             ->sortable();

        $grid->column('reason', __('Reason'))
             ->limit(50)
             ->help('Click to view full reason');

        // Actions column
        $grid->column('actions', __('Actions'))
             ->display(function () {
                 $actions = '';
                 
                 if ($this->canBeCancelled()) {
                     $actions .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger" onclick="cancelAppointment(' . $this->id . ')">Cancel</a> ';
                 }
                 
                 if ($this->status === 'scheduled') {
                     $actions .= '<a href="javascript:void(0)" class="btn btn-sm btn-primary" onclick="confirmAppointment(' . $this->id . ')">Confirm</a> ';
                 }
                 
                 if ($this->status === 'confirmed') {
                     $actions .= '<a href="javascript:void(0)" class="btn btn-sm btn-warning" onclick="checkinAppointment(' . $this->id . ')">Check In</a> ';
                 }
                 
                 if ($this->status === 'in_progress') {
                     $actions .= '<a href="javascript:void(0)" class="btn btn-sm btn-success" onclick="completeAppointment(' . $this->id . ')">Complete</a> ';
                 }
                 
                 if ($this->canBeRescheduled()) {
                     $actions .= '<a href="' . admin_url('appointments/' . $this->id . '/reschedule') . '" class="btn btn-sm btn-info">Reschedule</a> ';
                 }

                 return $actions ?: '<span class="text-muted">No actions</span>';
             });

        // Filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->equal('status', 'Status')->select(Appointment::getStatusOptions());
            $filter->equal('appointment_type', 'Type')->select(Appointment::getAppointmentTypes());
            $filter->equal('priority', 'Priority')->select(Appointment::getPriorityLevels());
            
            $filter->equal('doctor_id', 'Doctor')->select(
                User::where('user_type', 'doctor')->pluck('name', 'id')
            );
            
            $filter->equal('patient_id', 'Patient')->select(function ($id) {
                if ($id) {
                    return User::where('user_type', 'patient')->where('id', $id)->pluck('name', 'id');
                }
            })->ajax('/admin/api/patients');

            $filter->between('appointment_date', 'Appointment Date')->datetime();
            
            $filter->equal('department_id', 'Department')->select(
                Department::pluck('name', 'id')
            );
        });

        // Disable batch actions
        $grid->disableBatchActions();

        // Export
        $grid->exporter(new AppointmentExporter());

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
        $show = new Show(Appointment::findOrFail($id));

        $show->field('appointment_number', __('Appointment Number'));
        $show->field('patient.name', __('Patient Name'));
        $show->field('patient.phone', __('Patient Phone'));
        $show->field('doctor.name', __('Doctor Name'));
        $show->field('department.name', __('Department'));
        $show->field('appointment_date', __('Appointment Date'))->as(function ($date) {
            return Utils::my_date_time($date);
        });
        $show->field('appointment_end_date', __('End Time'))->as(function ($date) {
            return Utils::my_date_time($date);
        });
        $show->field('duration_minutes', __('Duration (minutes)'));
        $show->field('appointment_type', __('Type'))->using(Appointment::getAppointmentTypes());
        $show->field('priority', __('Priority'))->using(Appointment::getPriorityLevels());
        $show->field('status', __('Status'))->using(Appointment::getStatusOptions());
        $show->field('reason', __('Reason'));
        $show->field('notes', __('Notes'));
        $show->field('preparation_instructions', __('Preparation Instructions'));
        $show->field('room.name', __('Room'));
        $show->field('services_requested', __('Services Requested'))->json();
        
        // Timestamps and tracking
        $show->divider();
        $show->field('created_at', __('Created At'))->as(function ($date) {
            return Utils::my_date_time($date);
        });
        $show->field('createdBy.name', __('Created By'));
        
        if ($this->confirmed_at) {
            $show->field('confirmed_at', __('Confirmed At'))->as(function ($date) {
                return Utils::my_date_time($date);
            });
            $show->field('confirmedBy.name', __('Confirmed By'));
        }
        
        if ($this->checked_in_at) {
            $show->field('checked_in_at', __('Checked In At'))->as(function ($date) {
                return Utils::my_date_time($date);
            });
        }
        
        if ($this->completed_at) {
            $show->field('completed_at', __('Completed At'))->as(function ($date) {
                return Utils::my_date_time($date);
            });
        }
        
        if ($this->cancelled_at) {
            $show->field('cancelled_at', __('Cancelled At'))->as(function ($date) {
                return Utils::my_date_time($date);
            });
            $show->field('cancelledBy.name', __('Cancelled By'));
            $show->field('cancellation_reason', __('Cancellation Reason'));
        }

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Appointment());

        // Main appointment details
        $form->tab('Basic Information', function ($form) {
            $form->select('patient_id', __('Patient'))
                 ->options(User::where('user_type', 'patient')->pluck('name', 'id'))
                 ->rules('required')
                 ->ajax('/admin/api/patients');

            $form->select('doctor_id', __('Doctor'))
                 ->options(User::where('user_type', 'doctor')->pluck('name', 'id'))
                 ->rules('required');

            $form->select('department_id', __('Department'))
                 ->options(Department::pluck('name', 'id'));

            $form->datetime('appointment_date', __('Appointment Date & Time'))
                 ->rules('required|after:now')
                 ->format('YYYY-MM-DD HH:mm:ss');

            $form->number('duration_minutes', __('Duration (minutes)'))
                 ->default(30)
                 ->rules('required|min:15|max:480');

            $form->select('appointment_type', __('Appointment Type'))
                 ->options(Appointment::getAppointmentTypes())
                 ->default('consultation')
                 ->rules('required');

            $form->select('priority', __('Priority'))
                 ->options(Appointment::getPriorityLevels())
                 ->default('normal')
                 ->rules('required');

            $form->textarea('reason', __('Reason for Visit'))
                 ->rules('required|max:1000');

            $form->textarea('notes', __('Additional Notes'))
                 ->rules('max:1000');
        });

        // Resource booking
        $form->tab('Resources', function ($form) {
            $form->select('room_id', __('Room'))
                 ->options(Room::active()->pluck('name', 'id'))
                 ->help('Optional - assign a specific room for this appointment');

            $form->textarea('preparation_instructions', __('Preparation Instructions'))
                 ->help('Instructions for the patient before the appointment')
                 ->rules('max:1000');

            $form->multipleSelect('services_requested', __('Services Requested'))
                 ->options([
                     'consultation' => 'General Consultation',
                     'blood_test' => 'Blood Test',
                     'x_ray' => 'X-Ray',
                     'ultrasound' => 'Ultrasound',
                     'ecg' => 'ECG',
                     'vaccination' => 'Vaccination',
                     'physical_exam' => 'Physical Examination',
                     'follow_up' => 'Follow-up Care'
                 ]);
        });

        // Recurring appointments
        $form->tab('Recurring Options', function ($form) {
            $form->switch('is_recurring', __('Recurring Appointment'))
                 ->help('Create multiple appointments with regular intervals');

            $form->select('recurrence_type', __('Recurrence Type'))
                 ->options([
                     'daily' => 'Daily',
                     'weekly' => 'Weekly',
                     'monthly' => 'Monthly',
                     'yearly' => 'Yearly'
                 ])
                 ->when('is_recurring', 1);

            $form->number('recurrence_interval', __('Recurrence Interval'))
                 ->default(1)
                 ->help('Every X days/weeks/months/years')
                 ->when('is_recurring', 1);

            $form->date('recurrence_end_date', __('Recurrence End Date'))
                 ->help('When to stop creating recurring appointments')
                 ->when('is_recurring', 1);
        });

        // Save callback
        $form->saving(function (Form $form) {
            // Auto-generate appointment number if not set
            if (empty($form->appointment_number)) {
                $form->appointment_number = Appointment::generateAppointmentNumber(
                    auth()->user()->enterprise_id ?? 1
                );
            }

            // Set enterprise_id
            $form->enterprise_id = auth()->user()->enterprise_id ?? 1;
            $form->created_by = auth()->id();

            // Validate no conflicts
            $conflicts = Appointment::where('doctor_id', $form->doctor_id)
                ->where('id', '!=', $form->model()->id ?? 0)
                ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
                ->where(function ($q) use ($form) {
                    $appointmentDate = $form->appointment_date;
                    $endDate = Carbon::parse($appointmentDate)->addMinutes($form->duration_minutes);
                    
                    $q->whereBetween('appointment_date', [$appointmentDate, $endDate])
                      ->orWhereBetween('appointment_end_date', [$appointmentDate, $endDate])
                      ->orWhere(function ($q2) use ($appointmentDate, $endDate) {
                          $q2->where('appointment_date', '<=', $appointmentDate)
                             ->where('appointment_end_date', '>=', $endDate);
                      });
                })
                ->exists();

            if ($conflicts) {
                throw new \Exception('Doctor has conflicting appointments at this time. Please choose a different time slot.');
            }
        });

        // After save callback
        $form->saved(function (Form $form, $result) {
            $appointment = $form->model();
            
            // Create recurring appointments if enabled
            if ($appointment->is_recurring && $form->isCreating()) {
                $appointment->createRecurringAppointments();
            }
        });

        return $form;
    }

    /**
     * Calendar view
     */
    public function calendar(Content $content)
    {
        return $content
            ->title('Appointment Calendar')
            ->description('Visual calendar view of all appointments')
            ->body(view('admin.appointments.calendar'));
    }

    /**
     * Doctor schedule management
     */
    public function doctorSchedule(Content $content)
    {
        return $content
            ->title('Doctor Schedules')
            ->description('Manage doctor working hours and availability')
            ->body(view('admin.appointments.doctor-schedule'));
    }

    /**
     * API endpoint for appointment actions
     */
    public function appointmentAction(Request $request, $id, $action)
    {
        $appointment = Appointment::findOrFail($id);
        
        switch ($action) {
            case 'confirm':
                $result = $appointment->confirm();
                break;
            case 'cancel':
                $result = $appointment->cancel($request->get('reason'));
                break;
            case 'checkin':
                $result = $appointment->checkIn();
                break;
            case 'complete':
                $result = $appointment->complete();
                break;
            case 'no_show':
                $result = $appointment->markAsNoShow();
                break;
            default:
                return response()->json(['success' => false, 'message' => 'Invalid action']);
        }

        return response()->json([
            'success' => $result,
            'message' => $result ? ucfirst($action) . ' successful' : ucfirst($action) . ' failed'
        ]);
    }

    /**
     * Get available time slots for a doctor
     */
    public function getAvailableSlots(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $date = $request->get('date');
        $duration = $request->get('duration', 30);

        if (!$doctorId || !$date) {
            return response()->json(['error' => 'Doctor ID and date are required']);
        }

        $slots = Appointment::getAvailableSlots($doctorId, $date, $duration);

        return response()->json(['slots' => $slots]);
    }
}
