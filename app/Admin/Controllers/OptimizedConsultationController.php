<?php

namespace App\Admin\Controllers;

use App\Admin\Traits\EnterpriseControllerTrait;
use App\Admin\Traits\FormValidationTrait;
use App\Admin\Traits\GridConfigurationTrait;
use App\Models\Consultation;
use App\Models\Service;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OptimizedConsultationController extends AdminController
{
    use EnterpriseControllerTrait, FormValidationTrait, GridConfigurationTrait {
        // Resolve configureStandardGrid collision - prefer GridConfigurationTrait version
        GridConfigurationTrait::configureStandardGrid insteadof EnterpriseControllerTrait;
        EnterpriseControllerTrait::configureStandardGrid as configureEnterpriseGrid;
        
        // Resolve addStatusColumn collision - prefer GridConfigurationTrait version (more features)
        GridConfigurationTrait::addStatusColumn insteadof EnterpriseControllerTrait;
        EnterpriseControllerTrait::addStatusColumn as addEnterpriseStatusColumn;
        
        // Resolve addUserColumn collision - prefer GridConfigurationTrait version (more features)
        GridConfigurationTrait::addUserColumn insteadof EnterpriseControllerTrait;
        EnterpriseControllerTrait::addUserColumn as addEnterpriseUserColumn;
    }

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
        // Validate enterprise access
        $enterprise = $this->validateEnterprise();
        if (!$enterprise) {
            return redirect(admin_url('/'));
        }

        $grid = new Grid(new Consultation());
        
        // Apply enterprise scoping
        $this->applyEnterpriseScope($grid);
        
        // Configure standard grid settings  
        $this->configureStandardGrid($grid, [
            'quick_search' => ['patient_name', 'patient_contact'],
            'order_by' => 'created_at'
        ]);

        // Apply default filter for pending consultations
        $grid->model()->where('main_status', 'Pending');

        // Add standard columns
        $this->addIdColumn($grid);
        
        $grid->column('consultation_number', 'Consultation Number')
            ->sortable();

        $this->addDateColumn($grid, 'created_at', 'Date');
        
        $this->addUserColumn($grid, 'patient_id', 'Patient');
        $this->addUserColumn($grid, 'receptionist_id', 'Receptionist');

        $grid->column('patient_contact', 'Contact');
        
        $this->addTextColumn($grid, 'contact_address', 'Address', 30, true);
        
        $this->addDateColumn($grid, 'preferred_date_and_time', 'Consultation Date', true);
        
        $this->addTextColumn($grid, 'services_requested', 'Services Requested');
        
        $this->addTextColumn($grid, 'reason_for_consultation', 'Reason', 50, true);

        // Vital signs columns
        $this->addNumberColumn($grid, 'temperature', 'Temperature', 1);
        $this->addNumberColumn($grid, 'weight', 'Weight', 1);
        $this->addNumberColumn($grid, 'height', 'Height', 1);
        $this->addNumberColumn($grid, 'bmi', 'BMI', 2);

        // Preview action
        $grid->column('preview', 'Preview')
            ->display(function () {
                $link = url('medical-report?id=' . $this->getKey());
                return "<a href='$link' target='_blank' class='btn btn-sm btn-info'>Preview Report</a>";
            });

        // Status column with custom configuration
        $this->addStatusColumn($grid, 'main_status', [
            'Pending' => 'primary',
            'Approved' => 'success',
            'Completed' => 'success',
            'Ongoing' => 'info',
            'Rejected' => 'danger',
            'Cancelled' => 'danger',
            'Rescheduled' => 'warning'
        ]);

        $this->addTimestampColumns($grid);

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

        $show->field('id', 'ID');
        $show->field('consultation_number', 'Consultation Number');
        $show->field('patient_name', 'Patient Name');
        $show->field('patient_contact', 'Patient Contact');
        $show->field('contact_address', 'Address');
        $show->field('reason_for_consultation', 'Reason for Consultation');
        $show->field('services_requested', 'Services Requested');
        $show->field('preferred_date_and_time', 'Preferred Date/Time');
        
        $show->divider();
        
        $show->field('temperature', 'Temperature');
        $show->field('weight', 'Weight');
        $show->field('height', 'Height');
        $show->field('bmi', 'BMI');
        
        $show->divider();
        
        $show->field('main_status', 'Status');
        $show->field('payment_status', 'Payment Status');
        $show->field('created_at', 'Created At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        // Validate enterprise access
        $enterprise = $this->validateEnterprise();
        if (!$enterprise) {
            return redirect(admin_url('/'));
        }

        $form = new Form(new Consultation());

        // Add enterprise field
        $this->addEnterpriseField($form);

        $form->divider('Consultation Details');

        // Patient selection with AJAX
        $form->select('patient_id', 'Select Patient')
            ->options(function ($id) {
                $user = User::find($id);
                return $user ? [$user->id => "#{$user->id} - {$user->name}"] : [];
            })
            ->ajax($this->getUserAjaxUrl())
            ->rules('required|exists:users,id');

        // Receptionist selection
        $form->select('receptionist_id', 'Receptionist')
            ->options(function ($id) {
                $user = User::find($id);
                return $user ? [$user->id => "#{$user->id} - {$user->name}"] : [];
            })
            ->ajax($this->getUserAjaxUrl())
            ->rules('nullable|exists:users,id');

        // Patient information
        $form->text('patient_name', 'Patient Name')
            ->rules($this->getValidationRules()['required_string']);

        $form->text('patient_contact', 'Patient Contact')
            ->rules($this->getValidationRules()['required_phone']);

        $form->text('contact_address', 'Address')
            ->rules($this->getValidationRules()['optional_string']);

        $form->datetime('preferred_date_and_time', 'Preferred Date & Time')
            ->rules($this->getValidationRules()['optional_datetime']);

        $form->textarea('reason_for_consultation', 'Reason for Consultation')
            ->rules($this->getValidationRules()['required_text']);

        $form->textarea('services_requested', 'Services Requested')
            ->rules($this->getValidationRules()['optional_text']);

        $form->divider('Vital Signs');

        $form->decimal('temperature', 'Temperature (°C)')
            ->rules('nullable|numeric|between:30,50');

        $form->decimal('weight', 'Weight (kg)')
            ->rules('nullable|numeric|between:1,500');

        $form->decimal('height', 'Height (cm)')
            ->rules('nullable|numeric|between:30,300');

        $form->decimal('bmi', 'BMI')
            ->rules('nullable|numeric|between:10,60')
            ->readonly();

        $form->divider('Status');

        $this->addStatusField($form, [
            'Pending' => 'Pending',
            'Approved' => 'Approved', 
            'Ongoing' => 'Ongoing',
            'Completed' => 'Completed',
            'Cancelled' => 'Cancelled',
            'Rejected' => 'Rejected'
        ], 'main_status');

        // Custom form saving logic
        $form->saving(function (Form $form) {
            try {
                // Calculate BMI if height and weight are provided
                if ($form->weight && $form->height) {
                    $heightInMeters = $form->height / 100;
                    $form->bmi = round($form->weight / ($heightInMeters * $heightInMeters), 2);
                }

                // Auto-generate consultation number if not set
                if (!$form->consultation_number) {
                    $today = date('Y-m-d');
                    $count = Consultation::whereDate('created_at', $today)->count() + 1;
                    $form->consultation_number = date('Y-m-d') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
                }

            } catch (\Exception $e) {
                return $this->handleFormErrors($e, 'save');
            }
        });

        $form->saved(function () {
            $this->showSuccessMessage();
        });

        return $form;
    }
}
