<?php

namespace App\Admin\Controllers;

use App\Models\AdminRole;
use App\Models\Company;
use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Hash;


class CardsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });
        $conds = [];
        $u = Admin::user();
        if ($u != null && $u->company_id != 1) {
            $conds['company_id'] = $u->company_id;
        }
        $grid->model()->where([
            'is_dependent' => 'No',
        ])->orderBy('id', 'Desc');

        $grid->actions(function ($actions) {
            //$actions->disableView();
        });

        $grid->filter(function ($filter) {
            $roleModel = config('admin.database.roles_model');
            $filter->equal('main_role_id', 'Filter by role')
                ->select($roleModel::where('slug', '!=', 'super-admin')
                    ->where('slug', '!=', 'student')
                    ->get()
                    ->pluck('name', 'id'));

            $filter->equal('company_id', 'Company')->select(function () {
                $a = Company::where([])->get();
                $arr = [];
                foreach ($a as $b) {
                    $arr[$b->id] = $b->name;
                }
                return $arr;
            });
        });



        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->disableBatchActions();
        $grid->column('id', __('Id'))->sortable();

        /* $grid->column('avatar', __('Photo'))
            ->lightbox(['width' => 50, 'height' => 50]); */

        $grid->column('name', __('Name Owner'))->sortable();
        $grid->column('card_number', __('Card number'))->sortable();
        $grid->column('card_expiry', __('Card expiry'))->sortable();
        $grid->column('card_status', __('Card status'))
            ->label([
                'Pending' => 'warning',
                'Active' => 'success',
                'Deactive' => 'danger',
            ])->sortable();
        $grid->column('card_accepts_credit', __('Card accepts credit'))
            ->label([
                'Yes' => 'success',
                'No' => 'danger',
            ])->sortable();
        $grid->column('card_max_credit', __('Card credit limit'))->sortable();
        $grid->column('company_id', __('Company'))->display(function ($id) {
            if ($this->company == null) {
                return 'N/A';
            }
            return $this->company->name;
        })->sortable();
        $grid->column('card_balance', __('Card balance'))
            ->display(function ($x) {
                if ($x == null || $x == '') {
                    $x = 0;
                }
                return number_format($x);
            })->sortable()
            ->totalRow(function ($amount) {
                return "<b><span class='text-primary'>Total: " . number_format($amount) . "</span></b>";
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

        $u = Administrator::findOrFail($id);
        $tab = new Tab();
        $tab->add('Bio', view('admin.dashboard.show-user-profile-bio', [
            'u' => $u
        ]));
        return $tab;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $u = Admin::user();

        $form = new Form(new User());
        //hide user_type
        $form->hidden('user_type')->default('Patient');


        $form->divider('BIO DATA');

        $u = Admin::user();
        $form->display('first_name');
        $form->display('last_name');

        $form->divider('CARD SETTINGS');
        $form->disableCreatingCheck();

        $form->radioCard('is_dependent', 'Is this patient a dependent?')->options(['Yes' => 'Yes', 'No' => 'No'])
            ->when('Yes', function ($form) {
                /* dependent_id */
                $form->select('dependent_id', 'Select card')->options(User::where([
                    'user_type' => 'Patient',
                    'is_dependent' => 'No',
                ])->pluck('name', 'id'))->rules('required');

                /* dependent_status */
                $form->radioCard('dependent_status', 'Dependent status')->options(['Pending' => 'Pending', 'Active' => 'Active', 'Deactive' => 'Deactive']);
            })
            ->when('No', function ($form) {
                /* card_number */
                $form->text('card_number', 'Card number');
                /* card_accepts_credit */
                $form->radioCard('card_accepts_credit', 'Card accepts credit')->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->when('Yes', function ($form) {
                        $form->decimal('card_max_credit', 'Card credit limit')->rules('required');
                    });
                /* card_max_credit */
                /* card_expiry */
                $form->date('card_expiry', 'Card expiry')->rules('required');
                $form->radioCard('card_status', 'Card status')->options(['Pending' => 'Pending', 'Active' => 'Active', 'Deactive' => 'Deactive']);
            })
            ->rules('required');



        $permissionModel = config('admin.database.permissions_model');



        $form->disableReset();
        $form->disableViewCheck();
        return $form;
    }
}
