<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use App\Models\AdminRoleUser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class EnterpriseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Enterprises';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Enterprise());
        
        $grid->disableBatchActions();
        $grid->quickSearch('name')->placeholder('Search by enterprise name');
        
        $grid->column('id', __('ID'))->sortable();
        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return date('d-M-Y', strtotime($created_at));
            })
            ->sortable();
        $grid->column('name', __('Enterprise Name'))->sortable();
        $grid->column('short_name', __('Short name'));
        $grid->column('type', __('Type'))->sortable();
        $grid->column('phone_number', __('Phone number'))->sortable();
        $grid->column('email', __('Email'))->sortable();
        $grid->column('status', __('Status'))
            ->label([
                'Active' => 'success',
                'Inactive' => 'danger',
                'Suspended' => 'warning',
            ])
            ->sortable();
        $grid->column('max_users', __('Max Users'))->sortable();
        $grid->column('expiry', __('Expiry Date'))
            ->display(function ($expiry) {
                return $expiry ? date('d-M-Y', strtotime($expiry)) : 'No expiry';
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
        $show = new Show(Enterprise::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Enterprise Name'));
        $show->field('short_name', __('Short name'));
        $show->field('details', __('Details'));
        $show->field('logo', __('Logo'))->image();
        $show->field('phone_number', __('Phone number'));
        $show->field('email', __('Email'));
        $show->field('address', __('Address'));
        $show->field('expiry', __('Expiry'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('subdomain', __('Subdomain'));
        $show->field('type', __('Type'));
        $show->field('status', __('Status'));
        $show->field('timezone', __('Timezone'));
        $show->field('currency', __('Currency'));
        $show->field('language', __('Language'));
        $show->field('max_users', __('Max users'));
        $show->field('storage_limit', __('Storage limit'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Enterprise());

        $users = [];
        foreach (AdminRoleUser::where(['role_id' => 2])->get() as $key => $role) {
            if ($role->user == null) {
                $role->delete();
                continue;
            }
            $users[$role->user->id] = $role->user->name;
        }

        $form->select('administrator_id', __('Set Enterprise Owner'))
            ->options($users)
            ->rules('required');

        $form->text('name', __('Enterprise Name'))->rules('required');
        $form->text('short_name', __('Enterprise Short Name'))->rules('required');
        $form->textarea('details', __('Details'));
        $form->image('logo', __('Enterprise Logo'));
        
        $form->text('phone_number', __('Phone Number'))->rules('required');
        $form->text('phone_number_2', __('Phone number 2'));
        $form->text('email', __('Enterprise Email'))->rules('required|email');
        $form->textarea('address', __('Address'));
        
        $form->text('subdomain', __('Subdomain'))->rules('required|unique:enterprises,subdomain');
        $form->text('p_o_box', __('P.O Box'));
        $form->text('website', __('Website'));
        $form->text('motto', __('Motto'));
        
        $form->radio('type', __('Enterprise Type'))
            ->options([
                'Hospital' => 'Hospital',
                'Clinic' => 'Clinic',
                'Medical Center' => 'Medical Center',
                'Polyclinic' => 'Polyclinic',
                'Dispensary' => 'Dispensary',
                'Health Center' => 'Health Center',
            ])
            ->default('Hospital')
            ->rules('required');

        $form->select('timezone', __('Timezone'))
            ->options([
                'UTC' => 'UTC',
                'Africa/Nairobi' => 'Africa/Nairobi (EAT)',
                'Africa/Lagos' => 'Africa/Lagos (WAT)',
                'Africa/Cairo' => 'Africa/Cairo (EET)',
                'America/New_York' => 'America/New_York (EST)',
                'Europe/London' => 'Europe/London (GMT)',
                'Asia/Dubai' => 'Asia/Dubai (GST)',
            ])
            ->default('UTC')
            ->rules('required');

        $form->select('currency', __('Currency'))
            ->options([
                'USD' => 'US Dollar (USD)',
                'UGX' => 'Ugandan Shilling (UGX)',
                'KES' => 'Kenyan Shilling (KES)',
                'TZS' => 'Tanzanian Shilling (TZS)',
                'NGN' => 'Nigerian Naira (NGN)',
                'EUR' => 'Euro (EUR)',
                'GBP' => 'British Pound (GBP)',
            ])
            ->default('USD')
            ->rules('required');

        $form->select('language', __('Language'))
            ->options([
                'en' => 'English',
                'fr' => 'French',
                'es' => 'Spanish',
                'ar' => 'Arabic',
                'sw' => 'Swahili',
            ])
            ->default('en')
            ->rules('required');

        $form->number('max_users', __('Maximum Users'))->default(100)->rules('required|min:1');
        $form->number('storage_limit', __('Storage Limit (MB)'))->default(1000)->rules('required|min:100');
        
        $form->datetime('expiry', __('Expiry Date'));
        
        $form->radio('status', __('Status'))
            ->options([
                'Active' => 'Active',
                'Inactive' => 'Inactive', 
                'Suspended' => 'Suspended',
            ])
            ->default('Active')
            ->rules('required');

        $form->decimal('wallet_balance', __('Wallet Balance'))->default(0);
        $form->switch('can_send_messages', __('Can Send Messages'))->default(1);
        $form->switch('has_valid_lisence', __('Has Valid License'))->default(1);

        return $form;
    }
}
