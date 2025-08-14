<?php

namespace App\Admin\Controllers;

use App\Models\DoctorSchedule;
use App\Models\User;
use App\Models\Enterprise;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DoctorScheduleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Doctor Schedules';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DoctorSchedule());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('doctor.name', __('Doctor'));
        $grid->column('day_of_week', __('Day'))->display(function ($day) {
            return ucfirst($day);
        })->label([
            'monday' => 'primary',
            'tuesday' => 'info',
            'wednesday' => 'success',
            'thursday' => 'warning',
            'friday' => 'danger',
            'saturday' => 'secondary',
            'sunday' => 'dark'
        ]);
        $grid->column('start_time', __('Start Time'));
        $grid->column('end_time', __('End Time'));
        $grid->column('slot_duration_minutes', __('Slot Duration (min)'));
        $grid->column('max_patients_per_slot', __('Max Patients'));
        $grid->column('is_active', __('Active'))->display(function ($is_active) {
            return $is_active ? 'Yes' : 'No';
        })->label([
            1 => 'success',
            0 => 'danger',
        ]);
        $grid->column('effective_from', __('Effective From'));

        $grid->filter(function($filter) {
            $filter->equal('doctor_id', 'Doctor')->select(User::where('user_type', 'doctor')->pluck('name', 'id'));
            $filter->equal('day_of_week', 'Day')->select([
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday'
            ]);
            $filter->equal('is_active', 'Active')->select([0 => 'No', 1 => 'Yes']);
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
        $show = new Show(DoctorSchedule::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('doctor.name', __('Doctor'));
        $show->field('day_of_week', __('Day of Week'));
        $show->field('start_time', __('Start Time'));
        $show->field('end_time', __('End Time'));
        $show->field('slot_duration_minutes', __('Slot Duration (minutes)'));
        $show->field('break_duration_minutes', __('Break Duration (minutes)'));
        $show->field('max_patients_per_slot', __('Max Patients per Slot'));
        $show->field('buffer_time_minutes', __('Buffer Time (minutes)'));
        $show->field('is_active', __('Active'))->as(function ($is_active) {
            return $is_active ? 'Yes' : 'No';
        });
        $show->field('effective_from', __('Effective From'));
        $show->field('effective_to', __('Effective To'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DoctorSchedule());

        $form->select('doctor_id', __('Doctor'))
            ->options(User::where('user_type', 'doctor')->pluck('name', 'id'))
            ->required();
            
        $form->select('day_of_week', __('Day of Week'))
            ->options([
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday'
            ])
            ->required();
            
        $form->time('start_time', __('Start Time'))
            ->required();
            
        $form->time('end_time', __('End Time'))
            ->required();
            
        $form->number('slot_duration_minutes', __('Slot Duration (minutes)'))
            ->min(5)
            ->max(120)
            ->default(30)
            ->required();
            
        $form->number('break_duration_minutes', __('Break Duration (minutes)'))
            ->min(0)
            ->max(60)
            ->default(0);
            
        $form->number('max_patients_per_slot', __('Max Patients per Slot'))
            ->min(1)
            ->max(10)
            ->default(1)
            ->required();
            
        $form->number('buffer_time_minutes', __('Buffer Time (minutes)'))
            ->min(0)
            ->max(30)
            ->default(5);
            
        $form->date('effective_from', __('Effective From'))
            ->default(date('Y-m-d'))
            ->required();
            
        $form->date('effective_to', __('Effective To'))
            ->help('Leave blank for indefinite schedule');
            
        $form->switch('is_active', __('Active'))
            ->default(1);

        return $form;
    }
}
