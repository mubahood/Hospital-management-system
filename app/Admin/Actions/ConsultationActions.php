<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;
use Encore\Admin\Actions\Response;
use Illuminate\Database\Eloquent\Model;

/**
 * Approve Consultation Action
 */
class ApproveConsultationAction extends RowAction
{
    public $name = 'Approve';

    public function handle(Model $model)
    {
        $model->update([
            'main_status' => 'Approved',
            'request_remarks' => 'Consultation approved and ready to start',
        ]);

        return $this->response()->success('Consultation approved successfully')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to approve this consultation?');
    }
}

/**
 * Reject Consultation Action
 */
class RejectConsultationAction extends RowAction
{
    public $name = 'Reject';

    public function handle(Model $model)
    {
        $model->update([
            'main_status' => 'Rejected',
            'request_remarks' => 'Consultation request has been rejected',
        ]);

        return $this->response()->success('Consultation rejected')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to reject this consultation?', 'This action cannot be undone.');
    }
}

/**
 * Start Consultation Action
 */
class StartConsultationAction extends RowAction
{
    public $name = 'Start';

    public function handle(Model $model)
    {
        $model->update([
            'main_status' => 'Ongoing',
            'consultation_number' => $model->consultation_number ?: $model->generate_consultation_number(),
            'actual_start_time' => now(),
        ]);

        return $this->response()->success('Consultation started successfully')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Start this consultation now?');
    }
}

/**
 * Complete Consultation Action
 */
class CompleteConsultationAction extends RowAction
{
    public $name = 'Complete';

    public function handle(Model $model)
    {
        $model->update([
            'main_status' => 'Completed',
            'actual_end_time' => now(),
        ]);

        return $this->response()->success('Consultation completed successfully')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Mark this consultation as completed?');
    }
}

/**
 * Add Medical Service Action
 */
class AddMedicalServiceAction extends RowAction
{
    public $name = 'Add Service';

    public function handle(Model $model)
    {
        // Redirect to medical service creation with consultation pre-filled
        $url = admin_url("medical-services/create?consultation_id={$model->id}");
        return $this->response()->redirect($url);
    }
}

/**
 * Generate Report Action
 */
class GenerateReportAction extends RowAction
{
    public $name = 'Generate Report';

    public function handle(Model $model)
    {
        $url = url("medical-report?id={$model->id}");
        return $this->response()->redirect($url, true); // Open in new tab
    }
}

/**
 * View Billing Action
 */
class ViewBillingAction extends RowAction
{
    public $name = 'View Billing';

    public function handle(Model $model)
    {
        $url = admin_url("billing-items?consultation_id={$model->id}");
        return $this->response()->redirect($url);
    }
}

/**
 * View Patient History Action
 */
class ViewPatientHistoryAction extends RowAction
{
    public $name = 'Patient History';

    public function handle(Model $model)
    {
        $url = admin_url("consultations?patient_id={$model->patient_id}");
        return $this->response()->redirect($url);
    }
}
