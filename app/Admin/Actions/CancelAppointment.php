<?php

namespace App\Admin\Actions;

use App\Models\Consultation;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;

class CancelAppointment extends RowAction
{
    public $name = 'Cancel Appointment';

    public function handle(Model $model)
    {
        if ($model->appointment_status === 'cancelled') {
            return $this->response()->error('Appointment is already cancelled.');
        }

        if ($model->appointment_status === 'completed') {
            return $this->response()->error('Cannot cancel a completed appointment.');
        }

        try {
            $user = Admin::user();
            
            $model->appointment_status = 'cancelled';
            $model->cancelled_at = now();
            $model->cancelled_by = $user->id;
            $model->save();

            return $this->response()->success('Appointment cancelled successfully!')->refresh();
        } catch (\Exception $e) {
            return $this->response()->error('Error cancelling appointment: ' . $e->getMessage());
        }
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to cancel this appointment?', 'This action cannot be undone.');
    }

    public function display($row)
    {
        // Only show for scheduled or confirmed appointments
        return in_array($row->appointment_status, ['scheduled', 'confirmed']);
    }
}
