<?php

namespace App\Admin\Controllers;

use App\Models\MedicalService;
use App\Models\StockItem;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MedicalServiceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Medical Services';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MedicalService());
        $grid->disableBatchActions();
        $grid->model()->where([
            'status' => 'Pending',
        ])
            ->orWhere([
                'status' => 'Ongoing',
            ])
            ->orderBy('id', 'desc');
        $grid->column('consultation_id', __('Consultation'))
            ->display(function ($id) {
                if ($this->consultation == null) {
                    return 'N/A';
                }
                return $this->consultation->consultation_number;
            })->sortable();
        $grid->column('patient_id', __('Patient'))
            ->display(function ($id) {
                if ($this->consultation == null) {
                    return 'N/A';
                }
                if ($this->consultation->patient == null) {
                    return 'N/A';
                }
                return $this->consultation->patient->name;
            })->sortable();
        $grid->column('assigned_to_id', __('Specialist/Doctor'))
            ->display(function ($id) {
                if ($this->assigned_to == null) {
                    return 'N/A';
                }
                return $this->assigned_to->name;
            })->sortable();
        $grid->column('type', __('Service'))
            ->sortable();
        $grid->column('remarks', __('Remarks'))->hide();
        $grid->column('instruction', __('Instruction'))->sortable();
        $grid->column('specialist_outcome', __('Specialist outcome'))
            ->display(function ($outcome) {
                if ($outcome == null || $outcome == '') {
                    return '-';
                }
                return $outcome;
            })->sortable();

        $grid->column('created_at', __('Created'))
            ->display(function ($date) {
                return Utils::my_date_time($date);
            })->sortable();
        $grid->column('status', __('Status'))
            ->filter([
                'Pending' => 'Pending',
                'Ongoing' => 'Ongoing',
            ])
            ->label([
                'Pending' => 'warning',
                'Ongoing' => 'primary',
                'Completed' => 'success',
            ])
            ->sortable();
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
        $show = new Show(MedicalService::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('consultation_id', __('Consultation id'));
        $show->field('receptionist_id', __('Receptionist id'));
        $show->field('patient_id', __('Patient id'));
        $show->field('assigned_to_id', __('Assigned to id'));
        $show->field('type', __('Type'));
        $show->field('status', __('Status'));
        $show->field('remarks', __('Remarks'));
        $show->field('instruction', __('Instruction'));
        $show->field('specialist_outcome', __('Specialist outcome'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MedicalService());

        $form->display('type', __('Service Type'));
        $form->display('instruction', __('Instructions'));
        $form->divider();
        $form->radio('status', __('Status'))
            ->options([
                'Pending' => 'Pending',
                'Ongoing' => 'Ongoing',
                'Cancelled' => 'Cancelled',
                'Completed' => 'Completed',
            ])->rules('required');
        $form->text('specialist_outcome', __('Specialist Remarks'));
        $form->divider('Medical Services Offered');

        //has many MedicalServiceItem
        $form->hasMany('medical_service_items', function (Form\NestedForm $form) {
            $form->select('stock_item_id', __('Select Item'))
                ->options(StockItem::getDropdownOptions())
                ->rules('required');
            $form->decimal('quantity', __('Quantity'))->rules('required');
            $form->text('description', __('Description'))->rules('required');
            $form->file('file', __('Add an Attachment'))->uniqueName()->removable();
        });


        return $form;
    }
}
