<?php

namespace App\Admin\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Company;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CompanyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Companies';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Company());
        $grid->disableBatchActions();
        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Created'))
            ->hide()
            ->sortable();
        $grid->column('name', __('Company Name'))->sortable();
        $grid->column('short_name', __('Short name'))->hide();
        //$grid->column('logo', __('Logo'));
        $grid->column('phone_number', __('Phone number'))->sortable();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('email', __('Email'));
        $grid->column('website', __('Website'));
        $grid->column('subdomain', __('Subdomain'));
        $grid->column('address', __('Address'));


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
        $show = new Show(Company::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('short_name', __('Short name'));
        $show->field('details', __('Details'));
        $show->field('logo', __('Logo'));
        $show->field('phone_number', __('Phone number'));
        $show->field('phone_number_2', __('Phone number 2'));
        $show->field('p_o_box', __('P o box'));
        $show->field('email', __('Email'));
        $show->field('address', __('Address'));
        $show->field('website', __('Website'));
        $show->field('subdomain', __('Subdomain'));
        $show->field('color', __('Color'));
        $show->field('welcome_message', __('Welcome message'));
        $show->field('type', __('Type'));
        $show->field('wallet_balance', __('Wallet balance'));
        $show->field('can_send_messages', __('Can send messages'));
        $show->field('has_valid_lisence', __('Has valid lisence'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('dp_year', __('Dp year'));
        $show->field('active_year', __('Active year'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Company());
        $users = [];
        foreach (AdminRoleUser::where(['role_id' => 2])->get() as $key => $role) {
            if ($role->user == null) {
                $role->delete();
                continue;
            }
            $users[$role->user->id] = $role->user->name;
        }
        $form->select('administrator_id', __('Set Company Owner'))
            ->options($users)
            ->rules('required');

        $form->text('name', __('Company Name'))->rules('required');
        $form->text('short_name', __('Company Short Name'))->rules('required');
        $form->text('details', __('Details'));
        $form->image('logo', __('Company Logo'));
        $form->text('phone_number', __('Phone Number'))->rules('required');
        $form->text('phone_number_2', __('Phone number 2'));
        $form->text('p_o_box', __('P o box'));
        $form->text('email', __('Company Email'))->rules('required');
        $form->text('address', __('Address'));
        $form->text('website', __('Website'));
        $form->text('subdomain', __('Subdomain'));
        $form->color('color', __('Company Color'));
        $form->text('welcome_message', __('Welcome message'));
        $form->text('type', __('Type'));
        /* $form->text('can_send_messages', __('Can send messages'));
        $form->text('has_valid_lisence', __('Has valid lisence')); */

        /*         $form->number('dp_year', __('Dp year'));
        $form->number('active_year', __('Active year')); */

        return $form;
    }
}
