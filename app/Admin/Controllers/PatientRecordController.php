<?php

namespace App\Admin\Controllers;

use App\Models\Patient;
use App\Models\PatientRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PatientRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Patient Diagnosis';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PatientRecord());
        $grid->model()->orderBy('id', 'desc');
        $grid->disableBatchActions();

        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Date'))
            ->display(function ($date) {
                //to date and hours and minutes displayed
                return date('d-m-Y h:m A', strtotime($date));
            })->sortable();
        $grid->column('administrator_id', __('Docttor'))
            ->display(function ($id) {
                return $this->administrator->name;
            })
            ->hide()
            ->sortable();
        $grid->column('patient_id', __('Patient'))
            ->display(function ($id) {
                if ($this->patient_user == null) {
                    return '-';
                }
                return $this->patient_user->full_name;
            })
            ->sortable();

        $grid->column('existing_medical_conditions', __('Medications Conditions'))
            ->dot([
                'Yes' => 'danger',
                'No' => 'success',
            ])->sortable();
        $grid->column('medications_and_allergies', __('Allergies'))
            ->dot([
                'Yes' => 'danger',
                'No' => 'success',
            ])->sortable();
        $grid->column('history_of_smoking', __('History of smoking'))->dot([
            'Yes' => 'danger',
            'No' => 'success',
        ])->sortable();

        $grid->column('history_of_alcohol', __('History of alcohol'))->dot([
            'Yes' => 'danger',
            'No' => 'success',
        ])->sortable();

        $grid->column('past_surgeries_or_hospitalizations', __('Past surgeries or hospitalizations'))->hide();

        $grid->column('any_other_relevant_medical_history', __('Pregant'))->dot([
            'Yes' => 'danger',
            'No' => 'success',
        ])->sortable();
        $grid->column('periodontal_assessment_gum_health', __('Breast Feeding'))->dot([
            'Yes' => 'danger',
            'No' => 'success',
        ])->sortable();
        $grid->column('chief_complaint', __('Chief complaint'));
        $grid->column('date_of_the_last_dental_visit', __('Date of the last dental visit'))->hide();
        $grid->column('previous_dental_treatments', __('Previous dental treatments'))->hide();
        $grid->column('dental_insurance_information', __('Dental insurance information'))->hide();
        $grid->column('intraoral_and_extraoral_photographs', __('Intraoral and extraoral photographs'))->hide();
        $grid->column('radiographic_images', __('Radiographic images'))->hide();
        $grid->column('periodontal_assessment_gum_health', __('Periodontal assessment gum health'))->hide();
        $grid->column('oral_cancer_screening', __('Oral cancer screening'))->hide();
        $grid->column('tooth_charting_notations', __('Tooth charting notations'))->hide();
        $grid->column('occlusion_bite_assessment', __('Occlusion bite assessment'))->hide();
        $grid->column('proposed_dental_treatments', __('Proposed dental treatments'))->hide();
        $grid->column('priority_and_urgency_of_treatments', __('Priority and urgency of treatments'))->hide();;

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
        $show = new Show(PatientRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('patient_id', __('Patient id'));
        $show->field('medications_and_allergies', __('Medications and allergies'));
        $show->field('existing_medical_conditions', __('Existing medical conditions'));
        $show->field('past_surgeries_or_hospitalizations', __('Past surgeries or hospitalizations'));
        $show->field('history_of_smoking', __('History of smoking'));
        $show->field('history_of_alcohol', __('History of alcohol'));
        $show->field('any_other_relevant_medical_history', __('Any other relevant medical history'));
        $show->field('chief_complaint', __('Chief complaint'));
        $show->field('date_of_the_last_dental_visit', __('Date of the last dental visit'));
        $show->field('previous_dental_treatments', __('Previous dental treatments'));
        $show->field('dental_insurance_information', __('Dental insurance information'));
        $show->field('intraoral_and_extraoral_photographs', __('Intraoral and extraoral photographs'));
        $show->field('radiographic_images', __('Radiographic images'));
        $show->field('periodontal_assessment_gum_health', __('Periodontal assessment gum health'));
        $show->field('oral_cancer_screening', __('Oral cancer screening'));
        $show->field('tooth_charting_notations', __('Tooth charting notations'));
        $show->field('occlusion_bite_assessment', __('Occlusion bite assessment'));
        $show->field('diagnosis_outcome', __('Diagnosis outcome'));
        $show->field('proposed_dental_treatments', __('Proposed dental treatments'));
        $show->field('priority_and_urgency_of_treatments', __('Priority and urgency of treatments'));
        $show->field('cost_estimates', __('Cost estimates'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PatientRecord());

        //set administrator_id to current user id
        $form->hidden('administrator_id', __('Administrator id'))->default(auth('admin')->user()->id);

        //patient_id dropdown
        $form->select('patient_id', __('Patient'))->options(Patient::toSelectArray())->rules('required');

        $form->divider('Medical History');
        $form->textarea('existing_medical_conditions', __('Does the patient have any existing medical conditions?'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No'
            ])
            ->help('e.g., diabetes, hypertension');
        $form->radio('medications_and_allergies', __('Does the patient have any allergies or is he/she taking any medications?'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No'
            ]);

        $form->radio('past_surgeries_or_hospitalizations', __('Does the patient have any past surgeries or hospitalizations?'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No'
            ]);
        $form->radio('history_of_smoking', __('Does the patient have a history of smoking?'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No'
            ]);
        $form->radio('history_of_alcohol', __('Does the patient have a history of alcohol consumption?'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No'
            ]);
        $form->radio('any_other_relevant_medical_history', __('Is The Patient Prignant?'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No'
            ]);
        $form->radio('periodontal_assessment_gum_health', __('Is The Patient  Breast Feeding?'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No'
            ]);
        $form->textarea('chief_complaint', __('Chief complaint'));
        $form->date('date_of_the_last_dental_visit', __('Date of the last dental visit'));
        $form->text('previous_dental_treatments', __('Previous dental treatments'));
        $form->image('intraoral_and_extraoral_photographs', __('Intraoral and extraoral photographs'));
        $form->image('radiographic_images', __('Radiographic images'));
        $form->textarea('diagnosis_outcome', __('Diagnosis outcome'));


        return $form;
    }
}
