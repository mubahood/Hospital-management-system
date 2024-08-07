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
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });
        $conds = [];
        $u = Admin::user();
        if ($u != null && $u->company_id != 1) {
            $conds['company_id'] = $u->company_id;
        }
        $grid->model(
            'card_number',
            '!=',
            null
        )
            ->orderBy('id', 'Desc')
            ->where($conds);
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
        $form->text('first_name')->rules('required');
        $form->text('last_name')->rules('required');
        $form->date('date_of_birth');
        $form->text('place_of_birth');
        $form->radioCard('sex', 'Gender')->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');
        $form->text('phone_number_1', 'Mobile phone number')->rules('required');
        $form->text('phone_number_2', 'Home phone number');
        $form->text('current_address', 'Address')->rules('required');



        $form->divider('PERSONAL INFORMATION');

        $form->radioCard('has_personal_info', 'Add personal information?')
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->when('Yes', function ($form) {
                $form->text('religion');
                $form->text('nationality');
                $form->text('home_address');

                $form->text('spouse_name', "Spouse's name");
                $form->text('spouse_phone', "Spouse's phone number");
                $form->text('father_name', "Father's name");
                $form->text('father_phone', "Father's phone number");
                $form->text('mother_name', "Mother's name");
                $form->text('mother_phone', "Mother's phone number");

                $form->text('languages', "Languages/Dilect");
                $form->text('emergency_person_name', "Emergency person to contact name");
                $form->text('emergency_person_phone', "Emergency person to contact phone number");
            });

        $form->divider('COMPANY SETTINGS');
        $form->radioCard('belongs_to_company', 'Does this patient belong to a company?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->when('Yes', function ($form) {
                $form->select('company_id', 'Company')->options(Company::pluck('name', 'id'))->rules('required');
                $form->radioCard('belongs_to_company_status', 'Company Status')->options(['Pending' => 'Pending', 'Active' => 'Active', 'Deactive' => 'Deactive']);
            })->rules('required');
        $form->divider('CARD SETTINGS');

        $form->radioCard('is_dependent', 'Is this patient a dependent?')->options(['Yes' => 'Yes', 'No' => 'No'])
            ->when('Yes', function ($form) {
                /* dependent_id */
                $form->select('dependent_id', 'Dependent')->options(User::where([
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

        $form->divider('SYSTEM ACCOUNT');
        $form->image('avatar', 'Photo');

        $form->text('email', 'Email address')
            ->creationRules(["unique:admin_users"])
            ->rules();


        if ($form->isCreating()) {
            $form->password('password', 'Password')->rules('required|confirmed');
            $form->password('password_confirmation', 'Password Confirmation')->rules('required')
                ->default(function ($form) {
                    return $form->model()->password;
                });
        } else {

            $form->radio('change_password', 'Change Password')
                ->options([
                    'Change Password' => 'Change Password',
                    'Dont Change Password' => 'Dont Change Password'
                ])->when('Change Password', function ($form) {
                    $form->password('password', trans('admin.password'))->rules('confirmed');
                    $form->password('password_confirmation', trans('admin.password_confirmation'))
                        ->default(function ($form) {
                            return $form->model()->password;
                        });
                });
        }
        $form->ignore(['password_confirmation', 'change_password']);
        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });




        $form->disableReset();
        $form->disableViewCheck();
        return $form;
    }
}
