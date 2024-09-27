<?php

namespace App\Admin\Controllers;

use App\Models\CardRecord;
use App\Models\Company;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CardRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Card Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CardRecord());

        //quick search on $grid by card number
        $grid->quickSearch('card_id', 'payment_remarks')->placeholder('Search by card number or remarks');


        //$grid-> filter
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('card_id', 'Card')->select(function () {
                $a = User::where([])->get();
                $arr = [];
                foreach ($a as $b) {
                    $arr[$b->id] = "#" . $b->id . " - " . $b->card_number;
                }
                return $arr;
            });
            $filter->equal('company_id', 'Company')->select(function () {
                $a = Company::where([])->get();
                $arr = [];
                foreach ($a as $b) {
                    $arr[$b->id] = $b->name;
                }
                return $arr;
            });
            $filter->equal('type', 'Type')->radio([
                'Credit' => 'Credit',
                'Debit' => 'Debit',
            ]);
            $filter->date('payment_date', 'Payment Date');
            $filter->like('payment_remarks', 'Payment Remarks');
        });



        $grid->model()->orderBy('id', 'desc');
        $grid->disableBatchActions();
        $grid->column('id', __('Sn.'))->sortable();

        $grid->column('amount', __('Amount (UGX)'))
            ->display(function ($amount) {
                return number_format($amount, 2);
            })->sortable();
        $grid->column('card_id', __('Card'))
            ->display(function ($id) {
                if ($this->card == null) {
                    return 'N/A';
                }
                return $this->card->card_number;
            })->sortable();


        $grid->column('created_at', __('Created'))
            ->display(function ($date) {
                return \App\Models\Utils::my_date_time($date);
            })->sortable();

        $grid->column('company_id', __('Company'))
            ->display(function ($id) {
                if ($this->company == null) {
                    return 'N/A';
                }
                return $this->company->name;
            })->sortable();
        $grid->column('type', __('Type'))
            ->label([
                'Credit' => 'success',
                'Debit' => 'danger',
            ])->sortable();
        $grid->column('description', __('Description'))->sortable();
        $grid->column('balance', __('Balance'))->hide();
        $grid->column('payment_date', __('Payment Date'))
            ->display(function ($date) {
                return \App\Models\Utils::my_date_time($date);
            })->sortable();
        $grid->column('payment_remarks', __('Payment remarks'))
            ->display(function ($remarks) {
                return $remarks;
            })->sortable();

        $grid->disableActions();
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
        $show = new Show(CardRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('card_id', __('Card id'));
        $show->field('company_id', __('Company id'));
        $show->field('type', __('Type'));
        $show->field('description', __('Description'));
        $show->field('amount', __('Amount'));
        $show->field('balance', __('Balance'));
        $show->field('payment_date', __('Payment date'));
        $show->field('payment_remarks', __('Payment remarks'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CardRecord());
        $form->disableEditingCheck();

        $ajax_url = url(
            '/api/ajax-cards'
        );
        $form->select('card_id', "Select card")
            ->options(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->card_number];
                }
            })
            ->ajax($ajax_url)
            ->rules('required')
            ->required();

        // $form->number('company_id', __('Company id'));
        $form->radio('type', __('Transaction type'))->options([
            'Credit' => 'Credit (+)',
            'Debit' => 'Debit (-)',
        ])->rules('required')->required();
        // $form->text('description', __('Description'));
        $form->datetime('payment_date', __('Payment date'));
        $form->decimal('amount', __('Amount'))->rules('required')->required();
        $form->text('payment_remarks', __('Remarks'))->rules('required')->required();
        /*         $form->decimal('balance', __('Balance')); */

        return $form;
    }
}
