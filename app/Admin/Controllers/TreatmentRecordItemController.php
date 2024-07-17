<?php

namespace App\Admin\Controllers;

use App\Models\TreatmentRecordItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TreatmentRecordItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Affected Teeth';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TreatmentRecordItem());

        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();
            //filter by  treatment record
            $filter->equal('treatment_record_id', 'Filter by Treatment Record')
                ->select(\App\Models\TreatmentRecord::toSelectArray());
        });

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableBatchActions();

        $grid->model()->orderBy('id', 'desc');


        $grid->column('id', __('ID'))->sortable();
        $grid->column('created_at', __('Date'))
            ->display(function ($date) {
                //to date and hours and minutes displayed
                return date('d-m-Y h:m A', strtotime($date));
            })->sortable();
        $grid->column('updated_at', __('Updated'))
            ->display(function ($date) {
                //to date and hours and minutes displayed
                return date('d-m-Y h:m A', strtotime($date));
            })->sortable()
            ->hide();
        $grid->column('treatment_record_id', __('Treatment Record/Patient'))
            ->display(function ($id) {
                if ($this->treatment_record == null) {
                    return '-';
                }
                if ($this->treatment_record->patient_user == null) {
                    return '-';
                }
                return $this->treatment_record->patient_user->full_name . " - #" . $this->treatment_record->id;
            })
            ->sortable();
        $grid->column('tooth', __('Tooth'))
            ->sortable();
        $grid->column('finding', __('Finding'))
            ->filter([
                'CP' => 'CP',
                'DC' => 'DC',
                'OC' => 'OC',
                'GC' => 'GC',
                'STAINS' => 'STAINS',
                'CALCULUS' => 'CALCULUS',
            ])
            ->sortable();
        $grid->column('treatment', __('Treatment'))
            ->filter([
                'EXT' => 'EXT',
                'FILLING' => 'FILLING',
                'SCALING' => 'SCALING',
                'ROOT CANAL' => 'ROOT CANAL',
            ])
            ->sortable();
        $grid->column('status', __('Status'))
            ->sortable()

            ->filter([
                'Done' => 'Done',
                'Not Done' => 'Not Done',
                'Cancelled' => 'Cancelled',
            ])
            ->editable('select', [
                'Done' => 'Done',
                'Not Done' => 'Not Done',
                'Cancelled' => 'Cancelled',
            ]);

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
        $show = new Show(TreatmentRecordItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('treatment_record_id', __('Treatment record id'));
        $show->field('tooth', __('Tooth'));
        $show->field('finding', __('Finding'));
        $show->field('treatment', __('Treatment'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TreatmentRecordItem());

        $form->number('treatment_record_id', __('Treatment record id'));
        $form->text('tooth', __('Tooth'));
        $form->text('finding', __('Finding'));
        $form->text('treatment', __('Treatment'));
        $form->text('status', __('Status'));

        return $form;
    }
}
