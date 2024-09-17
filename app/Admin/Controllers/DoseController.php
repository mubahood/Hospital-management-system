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

class DoseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Dosages';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Consultation());
        $grid->disableCreation();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->model()->where([ 
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
            ->sortable()
            ->hide();
        $grid->column('reason_for_consultation', __('Reason for consultation'))
            ->sortable()
            ->hide();
        // $grid->column('request_status', __('Request status'));
        // $grid->column('request_date', __('Request Date'));
        // $grid->column('request_remarks', __('Request Remarks'));
        // $grid->column('receptionist_comment', __('Receptionist comment'));
        $grid->column('temperature', __('Temperature'))->sortable()->hide();
        $grid->column('weight', __('Weight'))->sortable()->hide();
        $grid->column('height', __('Height'))->sortable()->hide();
        $grid->column('bmi', __('Bmi'))->sortable()->hide();
        $grid->column('dose_items', __('Dose Items'))
            ->display(function ($dose_items) {
                $count = count($dose_items);
                return "<span class='label label-primary'>$count</span>";
            });

        //dosage_progress
        $grid->column('dosage_progress', __('Dosage Progress'))
            ->filter([
                0 => '0%',
                10 => '10%',
                20 => '20%',
                30 => '30%',
                40 => '40%',
                50 => '50%',
                60 => '60%',
                70 => '70%',
                80 => '80%',
                90 => '90%',
                100 => '100%',
            ], '0')->label([
                0 => 'danger',
                10 => 'danger',
                20 => 'danger',
                30 => 'danger',
                40 => 'danger',
                50 => 'warning',
                60 => 'warning',
                70 => 'warning',
                80 => 'warning',
                90 => 'success',
                100 => 'success',
            ], 'danger')->sortable();
        $grid->column('dosage_is_completed', __('Dosage Progress'))
            ->filter([
                'No' => 'No',
                'Yes' => 'Yes',
            ])->label([
                'No' => 'danger',
                'Yes' => 'success',
            ])->sortable();



        $grid->column('preview', __('Consultation Report'))->display(function () {
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

        $url = $_SERVER['REQUEST_URI'];
        $segments = explode('/', $url);
        //second last
        $id = $segments[count($segments) - 2];
        $item = Consultation::find($id);

        //method
        $method = $_SERVER['REQUEST_METHOD'];

        //check if is get 
        if ($item != null && $method == 'GET') {
        }



        $form->divider('Consultation Details');
        $u = Admin::user();


        $form->display('patient_name', __('Patient'));
        $form->display('consultation_number', __('Consultation number'));

        $form->hidden('receptionist_id', __('Receptionist id'))->default($u->id);


        $form->divider('Dose Items');
        $form->html('Press "New" to enter new dose item.');
        $form->hasMany('dose_items', null, function (Form\NestedForm $form) {
            $form->text('medicine', 'Medicine')->required();
            $form->decimal('quantity', __('Quantity'))->required();

            $form->radio('units', __('Units'))
                ->options([
                    'Tablets' => 'Tablets',
                    'Mills' => 'Mills',
                    'Drops' => 'Drops',
                    'Injection' => 'Injection',
                ])
                ->required();

            $form->radio('times_per_day', __('Number of Times per day'))
                ->options([
                    1 => '1',
                    2 => '2',
                    3 => '3',
                    4 => '4',
                ])
                ->required();
            $form->decimal('number_of_days', __('For how many day?'))->required();
        });

        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->disableReset();
        return $form;
    }
}
