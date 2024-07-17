<?php

namespace App\Admin\Controllers;

use App\Models\Client;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ClientController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Clients';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Client());
        $grid->model()->where('company_id', auth()->user()->id);
        $grid->disableBatchActions();
        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->column('logo', __('Logo'))
            ->lightbox(['width' => 60, 'height' => 60])
            ->sortable();
        $grid->column('name', __('Company Name'))->sortable();
        $grid->column('short_name', __('Short name'))->hide();
        $grid->column('phone_number', __('Phone number'))->sortable();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('p_o_box', __('P o box'))->hide();
        $grid->column('email', __('Email'))->sortable();
        $grid->column('website', __('Website'))->hide();
        $grid->column('address', __('Address'));
        $grid->column('details', __('Details'))->hide();
        $grid->column('created_at', __('Joined'))->sortable()
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            });
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
        $show = new Show(Client::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('name', __('Name'));
        $show->field('short_name', __('Short name'));
        $show->field('logo', __('Logo'));
        $show->field('color', __('Color'));
        $show->field('phone_number', __('Phone number'));
        $show->field('phone_number_2', __('Phone number 2'));
        $show->field('p_o_box', __('P o box'));
        $show->field('email', __('Email'));
        $show->field('website', __('Website'));
        $show->field('address', __('Address'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Client());
        $form->hidden('company_id')->value(auth()->user()->company_id);
        $form->text('name', __('Client Name'))->rules('required');
        $form->text('short_name', __('Client Short Name'))->rules('required');
        $form->image('logo', __('Client Logo'));
        $form->color('color', __('Color'));
        $form->text('phone_number', __('Phone number'));
        $form->text('phone_number_2', __('Phone number 2'));
        $form->textarea('p_o_box', __('P o box'));
        $form->text('email', __('Email'))->rules('required');
        $form->text('website', __('Website'));
        $form->text('address', __('Address'));
        $form->quill('details', __('Details'));

        return $form;
    }
}
