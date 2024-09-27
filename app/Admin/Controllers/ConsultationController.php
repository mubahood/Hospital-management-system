<?php

namespace App\Admin\Controllers;

use App\Models\Consultation;
use App\Models\Service;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ConsultationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Consultations';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Consultation());
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
            ->display(function ($id) {
                if ($this->patient == null) {
                    return 'N/A';
                }
                return $this->patient->name;
            })->sortable();
        $grid->column('receptionist_id', __('Receptionist'))
            ->display(function ($id) {
                if ($this->receptionist == null) {
                    return 'N/A';
                }
                return $this->receptionist->name;
            })->sortable()->hide();;


        $grid->column('patient_contact', __('Contact'));
        $grid->column('contact_address', __('Address'))->sortable()->hide();
        $grid->column('preferred_date_and_time', __('Consultation Date'))
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
            $link = url('medical-report?id=' . $this->id);
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
            ->options(Service::get_list())
            ->rules('required');
        $form->quill('reason_for_consultation', __('Consultation Details'))->rules('required');

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
                $form->hasMany('medical_services', __('Press the "New" button to add a Medical Services'), function (Form\NestedForm $form) {
                    $form->select('type', __('Select Service'))
                        ->options(Service::all()->pluck('name', 'name')->toArray())
                        ->rules('required');
                    // assigned_to_id assign to specialist or doctor
                    $form->select('assigned_to_id', __('Assigned To'))
                        ->options(User::get_doctors())
                        ->rules('required');
                    //status hide as hidden
                    $form->hidden('status', __('Status'))->default('Pending');
                    //instruction
                    $form->text('instruction', __('Instructions'));
                });
            });


        return $form;
    }
}
