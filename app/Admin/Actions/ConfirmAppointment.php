<?php

namespace App\Admin\Actions;

use App\Models\Consultation;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;

class ConfirmAppointment extends RowAction
{
    public $name = 'Confirm Appointment';

    public function handle(Model $model)
    {
        if ($model->appointment_status === 'confirmed') {
            return $this->response()->error('Appointment is already confirmed.');
        }

        if ($model->appointment_status === 'cancelled') {
            return $this->response()->error('Cannot confirm a cancelled appointment.');
        }

        if ($model->appointment_status === 'completed') {
            return $this->response()->error('Cannot confirm a completed appointment.');
        }

        try {
            $user = Admin::user();
            
            $model->appointment_status = 'confirmed';
            $model->confirmed_at = now();
            $model->confirmed_by = $user->id;
            $model->save();

            return $this->response()->success('Appointment confirmed successfully!')->refresh();
        } catch (\Exception $e) {
            return $this->response()->error('Error confirming appointment: ' . $e->getMessage());
        }
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to confirm this appointment?');
    }

    public function display($row)
    {
        // Only show for scheduled appointments
        return $row->appointment_status === 'scheduled';
    }
}
