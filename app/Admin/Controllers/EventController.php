<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class EventController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Events';

    /**
     * Make a grid builder.
     * 
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Event());
        $conditions = [];
        $u = auth()->user();


        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();
            $filter->equal('reminder_state', 'Filter by Reminder State')
                ->select([
                    'On' => 'On',
                    'Off' => 'Off',
                ]);
            $filter->equal('priority', 'Filter by Priority')
                ->select([
                    'Low' => 'Low',
                    'Medium' => 'Medium',
                    'High' => 'High',
                ]);
        });

        $grid->column('event_date', __('Event Date'))
            ->display(function ($t) {
                return Utils::my_date_time($t);
            })
            ->sortable();
        $grid->column('reminder_date', __('Reminder Date'))
            ->display(function ($t) {
                return Utils::my_date($t);
            })
            ->sortable();
        $grid->column('administrator_id', __('User'))
            ->display(function ($t) {
                return Administrator::find($t)->name;
            })
            ->sortable();

        $grid->column('event_conducted', __('Event Status'))
            ->dot([
                'Pending' => 'warning',
                'Conducted' => 'success',
                'Cancelled' => 'danger',
            ], 'warning')
            ->sortable();
        $grid->column('priority', __('Priority'))
            ->using([
                'Low' => 'Low',
                'Medium' => 'Medium',
                'High' => 'High',
            ], 'Medium')
            ->label([
                'Low' => 'success',
                'Medium' => 'warning',
                'High' => 'danger',
            ])
            ->sortable();
        $grid->column('description', __('Description'))->hide();
        $grid->column('outcome', __('Outcome'))->hide();
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
        $show = new Show(Event::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('reminder_state', __('Reminder state'));
        $show->field('priority', __('Priority'));
        $show->field('event_date', __('Event date'));
        $show->field('reminder_date', __('Reminder date'));
        $show->field('description', __('Description'));
        $show->field('remind_beofre_days', __('Remind beofre days'));
        $show->field('reminders_sent', __('Reminders sent'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Event());

        $form->hidden('administrator_id')->default(auth()->user()->id);
        $form->hidden('company_id')->default(auth()->user()->company_id);

        if (!$form->isEditing()) {
            $form->hidden('reminders_sent')->default('No');
        } else {
            $form->radio('reminders_sent', 'Re-Send Reminder')->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->default('No')
                ->rules('required');
        }

        $form->hidden('reminder_state')->default('On');
        $form->text('name', 'Event Title')->rules('required');
        $form->quill('description', 'Event Description')->rules('required');
        $form->datetime('event_date', __('Event Date'))->rules('required');
        $form->decimal('remind_beofre_days', __('Reminder Before Days'))
            ->rules('required')
            ->default(1);
        $form->radio('priority', 'Priority')->options([
            'Low' => 'Low',
            'Medium' => 'Medium',
            'High' => 'High',
        ])->default('Medium')
            ->rules('required');

        $form->multipleSelect('users_to_notify', 'Add users to notify')->options(
            Administrator::where([
                'company_id' => auth()->user()->company_id,
            ])->pluck('name', 'id')
        )->rules('required');
        $form->radioCard('event_conducted', 'Event status')->options([
            'Pending' => 'Pending',
            'Conducted' => 'Conducted',
            'Skipped' => 'Skipped',
        ])->default('Pending')
            ->rules('required');
        $form->quill('outcome', 'Event Outcome');
        //accepts images only 
        $form->multipleImage('images', 'Event Images')
            ->removable()
            ->uniqueName()
            ->sortable()
            ->attribute(['accept' => 'image/*']);

        $form->multipleFile('files', 'Event Files')
            ->removable()
            ->uniqueName()
            ->sortable()
            ->attribute(['accept' => 'file/*']);

        $form->disableReset();
        $form->disableViewCheck();
        return $form;
    }
}
