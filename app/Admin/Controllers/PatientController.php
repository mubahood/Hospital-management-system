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


class PatientController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Patients';

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
        $grid->model()
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

        $grid->column('avatar', __('Photo'))
            ->lightbox(['width' => 50, 'height' => 50]);

        $grid->column('name', __('Name'))->sortable();
        $grid->column('roles', 'Roles')->pluck('name')->label()->hide();
        $grid->column('phone_number_1', __('Phone number'))->sortable();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('email', __('Email'));
        $grid->column('date_of_birth', __('D.O.B'))->sortable();
        $grid->column('nationality', __('Nationality'))->sortable()->hide();
        $grid->column('sex', __('Gender'))->sortable();

        $grid->column('home_address', __('Home address'))->hide();
        $grid->column('current_address', __('Current address'))->hide();
        $grid->column('religion', __('Religion'))->hide();
        $grid->column('spouse_name', __('Spouse name'))->hide();
        $grid->column('spouse_phone', __('Spouse phone'))->hide();
        $grid->column('father_name')->hide();
        $grid->column('father_phone')->hide();
        $grid->column('mother_name')->hide();
        $grid->column('mother_phone')->hide();
        $grid->column('languages')->hide();
        $grid->column('emergency_person_name', 'Next of Kin');
        $grid->column('company_id', 'Company')
            ->display(function () {
                if ($this->company == null) {
                    return 'N/A';
                }
                return $this->company->name;
            })->sortable();
        $grid->column('emergency_person_phone')->hide();
        $grid->column('national_id_number', 'N.I.N')->hide();
        $grid->column('passport_number')->hide();
        $grid->column('tin', 'TIN')->hide();
        $grid->column('nssf_number')->hide();
        $grid->column('bank_name')->hide();
        $grid->column('bank_account_number')->hide();
        $grid->column('primary_school_name')->hide();
        $grid->column('primary_school_year_graduated')->hide();
        $grid->column('seconday_school_name')->hide();
        $grid->column('seconday_school_year_graduated')->hide();
        $grid->column('high_school_name')->hide();
        $grid->column('high_school_year_graduated')->hide();
        $grid->column('degree_university_name')->hide();
        $grid->column('degree_university_year_graduated')->hide();
        $grid->column('masters_university_name')->hide();
        $grid->column('masters_university_year_graduated')->hide();
        $grid->column('phd_university_name')->hide();
        $grid->column('belongs_to_company', 'Belongs to Company')
            ->using(
                [
                    'Yes' => 'Yes',
                    'No' => 'No',
                ],
                'No'
            )
            ->label([
                'Yes' => 'success',
                'No' => 'danger',
            ])->sortable();


        $grid->column('week', 'Report')
            ->display(function ($x) {
                $url = url("/departmental-workplan?id={$this->id}");
                $link = '<a target="_blank" class="btn btn-primary btn-sm" href="' . $url . '">PRINT REPORT</a>';
                return $link;
            })->hide();

        $grid->column('password', __('Reset Password'))->display(function ($x) {
            $url = url("/reset-mail?id={$this->id}");
            $link = '<a target="_blank" class="btn btn-primary btn-sm" href="' . $url . '">RESET PASSWORD</a>';
            return $link;
        })->hide();
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
