<?php

namespace App\Admin\Controllers;

use App\Models\Patient;
use App\Models\TreatmentRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TreatmentRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Treatment Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TreatmentRecord());

        $grid->model()->orderBy('id', 'desc');
        $grid->disableBatchActions();

        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();
            //filter by patient
            $filter->equal('patient_id', 'Filter by Patient')->select(Patient::toSelectArray());
        });

        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Date'))
            ->display(function ($date) {
                //to date and hours and minutes displayed
                return date('d-m-Y h:m A', strtotime($date));
            })->sortable();
        $grid->column('patient_id', __('Patient'))
            ->display(function ($id) {
                if ($this->patient_user == null) {
                    return '-';
                }
                return $this->patient_user->full_name;
            })
            ->sortable();

        $grid->column('items', __('Affetced Teeth'))
            ->display(function ($items) {
                $items = array_map(function ($item) {
                    return "<span class='label label-danger'>{$item['tooth']}</span>";
                }, $items);
                return join('&nbsp;', $items);
            });
        $grid->column('items_count', __('Affetced Teeth Count'))
            ->display(function ($items) {
                return count($this->items);
            })->sortable();
        $grid->column('action', __('Action'))
            ->display(function ($id) {
                return "<a href='".admin_url('treatment-record-items')."?treatment_record_id={$this->id}' class='btn btn-xs btn-primary'>UPDATE</a>";
            });

        $grid->column('administrator_id', __('Doctor'))
            ->display(function ($id) {
                return $this->administrator->name;
            });
        return $grid;
        $grid->column('procedure', __('Procedure'))
            ->dot([
                'Extraction' => 'danger',
                'Filling' => 'success',
                'Cleaning' => 'info',
                'Root Canal' => 'warning',
                'Crown' => 'primary',
                'Bridge' => 'default',
                'Implant' => 'danger',
                'Denture' => 'success',
                'Braces' => 'info',
                'Invisalign' => 'warning',
                'Other' => 'primary',
            ])->filter([
                'Extraction' => 'Extraction',
                'Filling' => 'Filling',
                'Cleaning' => 'Cleaning',
                'Root Canal' => 'Root Canal',
                'Crown' => 'Crown',
                'Bridge' => 'Bridge',
                'Implant' => 'Implant',
                'Denture' => 'Denture',
                'Braces' => 'Braces',
                'Invisalign' => 'Invisalign',
                'Other' => 'Other',
            ])->sortable();
        /*  
                $form->decimal('upper_canines', __('Number of Upper Canines'));
                $form->decimal('upper_premolars', __('Number of Upper Premolars'));
                $form->decimal('upper_molars', __('Number of Upper Molars'));
                $form->decimal('lower_incisors', __('Number of Lower Incisors'));
                $form->decimal('lower_canines', __('Number of Lower Canines'));
                $form->decimal('lower_premolars', __('Number of Lower Premolars'));
                $form->decimal('lower_molars', __('Number of Lower Molars'));
 */

        $grid->column('upper_incisors', __('Upper Incisors'))->sortable();
        $grid->column('upper_canines', __('Upper Canines'))->sortable();
        $grid->column('upper_premolars', __('Upper Premolars'))->sortable();
        $grid->column('upper_molars', __('Upper Molars'))->sortable();
        $grid->column('lower_incisors', __('Lower Incisors'))->sortable();
        $grid->column('lower_canines', __('Lower Canines'))->sortable();
        $grid->column('lower_premolars', __('Lower Premolars'))->sortable();
        $grid->column('lower_molars', __('Lower Molars'))->sortable();

        $grid->column('details', __('Details'))->hide();
        $grid->column('photos', __('Photos'))->gallery(
            ['width' => 60, 'height' => 60, 'zooming' => true, 'class' => 'rounded']
        );

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
        $show = new Show(TreatmentRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('patient_id', __('Patient id'));
        $show->field('procedure', __('Procedure'));
        $show->field('teeth_extracted', __('teeth extracted'));
        $show->field('details', __('Details'));
        $show->field('photos', __('Photos'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TreatmentRecord());

        $form->hidden('administrator_id', __('Administrator id'))->default(auth('admin')->user()->id);
        $form->select('patient_id', __('Patient'))->options(Patient::toSelectArray())->rules('required');
        $tooth = [];
        $form->hasMany('items', 'tooth', function ($form) {
            $form->radio('tooth', __('tooth'))
                ->options([
                    '11' => '11',
                    '12' => '12',
                    '13' => '13',
                    '14' => '14',
                    '15' => '15',
                    '16' => '16',
                    '17' => '17',
                    '18' => '18',
                    '20' => '20',
                    '21' => '21',
                    '22' => '22',
                    '23' => '23',
                    '24' => '24',
                    '25' => '25',
                    '26' => '26',
                    '27' => '27',
                    '28' => '28',
                    '30' => '30',
                    '31' => '31',
                    '32' => '32',
                    '33' => '33',
                    '34' => '34',
                    '35' => '35',
                    '36' => '36',
                    '37' => '37',
                    '38' => '38',
                    '40' => '40',
                    '41' => '41',
                    '42' => '42',
                    '43' => '43',
                    '44' => '44',
                    '45' => '45',
                    '46' => '46',
                    '47' => '47',
                    '48' => '48',
                    '51' => '51',
                    '52' => '52',
                    '53' => '53',
                    '54' => '54',
                    '55' => '55',
                    '61' => '61',
                    '62' => '62',
                    '63' => '63',
                    '64' => '64',
                    '65' => '65',
                    '71' => '71',
                    '72' => '72',
                    '73' => '73',
                    '74' => '74',
                    '75' => '75',
                    '81' => '81',
                    '82' => '82',
                    '83' => '83',
                    '84' => '84',
                    '85' => '85',


                ])
                ->rules('required');
            $form->radio('finding', __('Clinical Finding'))
                ->options([
                    'CP' => 'CP',
                    'DC' => 'DC',
                    'OC' => 'OC',
                    'GC' => 'GC',
                    'RR' => 'RR',
                    'Malaligment' => 'Malaligment',
                    'Shading' => 'Shading',
                    'STAINS' => 'STAINS',
                    'CALCULUS' => 'CALCULUS',
                ]);
            $form->radio('treatment', __('Treatment Options'))
                ->options([
                    'EXT' => 'EXT',
                    'TF' => 'TF',
                    'FILLING' => 'FILLING',
                    'SCALING' => 'SCALING',
                    'ROOT CANAL' => 'ROOT CANAL',
                ]);
            $form->radio('status', __('Treatment Done'))
                ->options([
                    'Done' => 'Done',
                    'Not Done' => 'Not Done',
                ]);
        });
        $form->multipleImage('photos', __('Photos'));
        $form->textarea('details', __('Details'));
        return $form;

        $form->radio('procedure', __('Dental Procedure'))
            ->options([
                'Extraction' => 'Extraction',
                'Filling' => 'Filling',
                'Cleaning' => 'Cleaning',
                'Root Canal' => 'Root Canal',
                'Crown' => 'Crown',
                'Bridge' => 'Bridge',
                'Implant' => 'Implant',
                'Denture' => 'Denture',
                'Braces' => 'Braces',
                'Invisalign' => 'Invisalign',
                'Other' => 'Other',
            ])->when('Other', function (Form $form) {
                $form->textarea('procedure_other', __('Other Procedure'));
            })->when('in', [
                'Extraction',
                'Filling',
                'Root Canal',
                'Crown',
                'Bridge',
                'Implant',
                'Denture',
                'Braces',
                'Invisalign',
                'Other',
            ], function (Form $form) {
                $form->decimal('upper_incisors', __('Number of Upper Incisors'));
                $form->decimal('upper_canines', __('Number of Upper Canines'));
                $form->decimal('upper_premolars', __('Number of Upper Premolars'));
                $form->decimal('upper_molars', __('Number of Upper Molars'));
                $form->decimal('lower_incisors', __('Number of Lower Incisors'));
                $form->decimal('lower_canines', __('Number of Lower Canines'));
                $form->decimal('lower_premolars', __('Number of Lower Premolars'));
                $form->decimal('lower_molars', __('Number of Lower Molars'));
            })->rules('required');


        return $form;
    }
}
