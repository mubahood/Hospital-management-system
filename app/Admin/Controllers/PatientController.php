<?php

namespace App\Admin\Controllers;

use App\Models\AdminRole;
use App\Models\Company;
use App\Models\User;
use App\Models\Enterprise;
use App\Traits\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Builder;
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
        $u = Admin::user();
        $ent = Enterprise::find($u->enterprise_id);
        if ($ent == null) {
            admin_error('No enterprise found. Please contact your system administrator.');
            return redirect(admin_url('/'));
        }

        $grid = new Grid(new User());
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });
        
        // Enterprise filtering - Users will be automatically filtered by EnterpriseScopeTrait
        $grid->model()
            ->where('user_type', 'patient')
            ->orderBy('id', 'Desc');
            
        $grid->actions(function ($actions) {
            //$actions->disableView();
        });

        // Enhanced filters leveraging our model scopes
        $grid->filter(function ($filter) {
            $filter->like('name', 'Name');
            $filter->like('phone_number_1', 'Phone Number');
            $filter->like('email', 'Email');
            $filter->equal('sex', 'Gender')->select(['Male' => 'Male', 'Female' => 'Female']);
            $filter->between('date_of_birth', 'Date of Birth')->date();
            $filter->equal('belongs_to_company', 'Company Member')->select(['Yes' => 'Yes', 'No' => 'No']);
            $filter->equal('is_dependent', 'Is Dependent')->select(['Yes' => 'Yes', 'No' => 'No']);
            $filter->equal('card_status', 'Card Status')->select([
                'Pending' => 'Pending', 
                'Active' => 'Active', 
                'Deactive' => 'Deactive'
            ]);
            $filter->select('company_id', 'Company')->options(Company::pluck('name', 'id'));
            $filter->between('created_at', 'Registration Date')->datetime();
        });

        $grid->quickSearch('name', 'phone_number_1', 'email')->placeholder('Search by name, phone, or email');
        $grid->disableBatchActions();
        
        // Enhanced grid columns with accessor methods from User model
        $grid->column('id', __('ID'))->sortable();
        $grid->column('avatar', __('Photo'))
            ->lightbox(['width' => 50, 'height' => 50]);
        $grid->column('patient_number', __('Patient ID'))->sortable();
        $grid->column('full_name', __('Full Name'))->sortable();
        $grid->column('age', __('Age'))->sortable();
        $grid->column('sex', __('Gender'))->sortable()
            ->label(['Male' => 'primary', 'Female' => 'success']);
        $grid->column('formatted_phone', __('Phone'))->sortable();
        $grid->column('email', __('Email'));
        $grid->column('formatted_date_of_birth', __('D.O.B'))->sortable();
        
        // Company information
        $grid->column('company_id', 'Company')
            ->display(function ($company_id) {
                if (!$company_id) {
                    return '<span class="label label-default">N/A</span>';
                }
                $company = Company::withoutGlobalScope('enterprise')->find($company_id);
                if ($company == null) {
                    return '<span class="label label-warning">Invalid</span>';
                }
                return '<span class="label label-info">' . $company->name . '</span>';
            })->sortable();
            
        $grid->column('belongs_to_company', 'Company Member')
            ->using(['Yes' => 'Yes', 'No' => 'No'], 'No')
            ->label(['Yes' => 'success', 'No' => 'default'])->sortable();
            
        // Card information
        $grid->column('is_dependent', 'Dependent')
            ->using(['Yes' => 'Yes', 'No' => 'No'], 'No')
            ->label(['Yes' => 'warning', 'No' => 'success'])->sortable();
            
        $grid->column('card_status', 'Card Status')
            ->label([
                'Pending' => 'warning',
                'Active' => 'success',
                'Deactive' => 'danger',
            ])->sortable();
            
        // Emergency contact
        $grid->column('emergency_person_name', 'Emergency Contact');
        $grid->column('emergency_person_phone', 'Emergency Phone')->hide();
        
        // Medical information (hide by default, show on demand)
        $grid->column('insurance_policy_number', 'Insurance')->hide();
        $grid->column('medical_history', 'Medical History')->hide();
        $grid->column('allergies', 'Allergies')->hide();
        $grid->column('current_medications', 'Current Medications')->hide();
        
        // Statistics column
        $grid->column('stats', 'Statistics')
            ->display(function () {
                $consultationCount = $this->consultations()->count();
                $totalCost = $this->getTotalMedicalCosts();
                return "Consultations: {$consultationCount}<br>Total Cost: UGX " . number_format($totalCost);
            })->hide();
            
        // Additional hidden columns for detailed view
        $grid->column('nationality', __('Nationality'))->hide();
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
        $grid->column('national_id_number', 'N.I.N')->hide();
        $grid->column('passport_number')->hide();
        $grid->column('tin', 'TIN')->hide();
        $grid->column('time_ago', 'Registered')->sortable();

        // Action buttons
        $grid->column('actions', 'Actions')
            ->display(function ($x) {
                $patientId = $this->getKey();
                $consultationUrl = admin_url("/consultations?patient_id={$patientId}");
                $reportUrl = url("/departmental-workplan?id={$patientId}");
                $resetUrl = url("/reset-mail?id={$patientId}");
                
                $actions = '';
                $actions .= '<a target="_blank" class="btn btn-sm btn-primary" href="' . $consultationUrl . '" title="View Consultations">
                    <i class="fa fa-stethoscope"></i>
                </a> ';
                $actions .= '<a target="_blank" class="btn btn-sm btn-info" href="' . $reportUrl . '" title="Print Report">
                    <i class="fa fa-print"></i>
                </a> ';
                $actions .= '<a target="_blank" class="btn btn-sm btn-warning" href="' . $resetUrl . '" title="Reset Password">
                    <i class="fa fa-key"></i>
                </a>';
                
                return $actions;
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
        $ent = Enterprise::find($u->enterprise_id);
        if ($ent == null) {
            admin_error('No enterprise found. Please contact your system administrator.');
            return redirect(admin_url('/'));
        }

        $form = new Form(new User());
        
        // Hidden enterprise_id field
        $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
            ->value($u->enterprise_id);
        
        //hide user_type
        $form->hidden('user_type')->default('patient');

        $form->divider('BASIC INFORMATION');

        $form->text('first_name', 'First Name')->rules('required');
        $form->text('last_name', 'Last Name')->rules('required');
        $form->text('middle_name', 'Middle Name');
        $form->date('date_of_birth', 'Date of Birth')->rules('required');
        $form->text('place_of_birth', 'Place of Birth');
        $form->radioCard('sex', 'Gender')->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');
        $form->text('nationality', 'Nationality')->default('Ugandan');
        $form->text('national_id_number', 'National ID Number');
        $form->text('passport_number', 'Passport Number');

        $form->divider('CONTACT INFORMATION');

        $form->text('phone_number_1', 'Primary Phone Number')->rules('required');
        $form->text('phone_number_2', 'Secondary Phone Number');
        $form->email('email', 'Email Address')->rules('email');
        $form->textarea('current_address', 'Current Address')->rules('required');
        $form->textarea('home_address', 'Home/Permanent Address');

        $form->divider('MEDICAL INFORMATION');

        $form->textarea('medical_history', 'Medical History')
            ->help('Previous medical conditions, surgeries, hospitalizations');
        $form->textarea('allergies', 'Allergies')
            ->help('Known allergies to medications, foods, or substances');
        $form->textarea('current_medications', 'Current Medications')
            ->help('List all current medications and dosages');
        $form->textarea('family_medical_history', 'Family Medical History')
            ->help('Relevant family medical conditions');
        $form->select('blood_type', 'Blood Type')->options([
            'A+' => 'A+', 'A-' => 'A-',
            'B+' => 'B+', 'B-' => 'B-',
            'AB+' => 'AB+', 'AB-' => 'AB-',
            'O+' => 'O+', 'O-' => 'O-',
        ]);
        $form->text('weight', 'Weight (kg)')->help('Current weight in kilograms');
        $form->text('height', 'Height (cm)')->help('Height in centimeters');
        
        $form->divider('EMERGENCY CONTACT');

        $form->text('emergency_person_name', 'Emergency Contact Name')->rules('required');
        $form->text('emergency_person_phone', 'Emergency Contact Phone')->rules('required');
        $form->text('emergency_person_relationship', 'Relationship to Patient')
            ->help('e.g., Spouse, Parent, Sibling, Friend');
        $form->textarea('emergency_person_address', 'Emergency Contact Address');

        $form->divider('INSURANCE INFORMATION');

        $form->radioCard('has_insurance', 'Has Medical Insurance?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->when('Yes', function ($form) {
                $form->text('insurance_company', 'Insurance Company');
                $form->text('insurance_policy_number', 'Policy Number');
                $form->date('insurance_expiry_date', 'Policy Expiry Date');
                $form->text('insurance_coverage_details', 'Coverage Details');
            });

        $form->divider('FAMILY INFORMATION');

        $form->radioCard('has_family_info', 'Add family information?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->when('Yes', function ($form) {
                $form->text('religion', 'Religion');
                $form->text('spouse_name', "Spouse's Name");
                $form->text('spouse_phone', "Spouse's Phone Number");
                $form->text('father_name', "Father's Name");
                $form->text('father_phone', "Father's Phone Number");
                $form->text('mother_name', "Mother's Name");
                $form->text('mother_phone', "Mother's Phone Number");
                $form->text('languages', 'Languages Spoken');
            });

        $form->divider('EMPLOYMENT & COMPANY');
        
        $form->radioCard('belongs_to_company', 'Does this patient belong to a company?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->when('Yes', function ($form) {
                $form->select('company_id', 'Company')->options(Company::pluck('name', 'id'))->rules('required');
                $form->text('employee_id', 'Employee ID');
                $form->text('job_title', 'Job Title');
                $form->text('department', 'Department');
                $form->radioCard('belongs_to_company_status', 'Employment Status')
                    ->options(['Pending' => 'Pending', 'Active' => 'Active', 'Deactive' => 'Inactive']);
            })->rules('required');

        $form->divider('FINANCIAL INFORMATION');

        $form->radioCard('add_financial_info', 'Add financial information?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->when('Yes', function ($form) {
                $form->text('tin', 'TIN Number');
                $form->text('nssf_number', 'NSSF Number');
                $form->text('bank_name', 'Bank Name');
                $form->text('bank_account_number', 'Bank Account Number');
                $form->text('next_of_kin', 'Next of Kin');
                $form->text('next_of_kin_phone', 'Next of Kin Phone');
            });

        $form->divider('CARD SETTINGS');

        $form->radioCard('is_dependent', 'Is this patient a dependent?')->options(['Yes' => 'Yes', 'No' => 'No'])
            ->when('Yes', function ($form) {
                $form->select('dependent_id', 'Primary Cardholder')->options(User::where([
                    'user_type' => 'patient',
                    'is_dependent' => 'No',
                ])->pluck('name', 'id'))->rules('required');
                $form->radioCard('dependent_status', 'Dependent Status')
                    ->options(['Pending' => 'Pending', 'Active' => 'Active', 'Deactive' => 'Inactive']);
            })
            ->when('No', function ($form) {
                $form->text('card_number', 'Card Number');
                $form->radioCard('card_accepts_credit', 'Card Accepts Credit')
                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                    ->when('Yes', function ($form) {
                        $form->decimal('card_max_credit', 'Card Credit Limit')->rules('required');
                    });
                $form->date('card_expiry', 'Card Expiry Date')->rules('required');
                $form->radioCard('card_status', 'Card Status')
                    ->options(['Pending' => 'Pending', 'Active' => 'Active', 'Deactive' => 'Inactive']);
            })
            ->rules('required');

        $form->divider('EDUCATION (Optional)');

        $form->radioCard('add_education_info', 'Add education information?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->when('Yes', function ($form) {
                $form->text('primary_school_name', 'Primary School');
                $form->text('primary_school_year_graduated', 'Primary Graduation Year');
                $form->text('seconday_school_name', 'Secondary School');
                $form->text('seconday_school_year_graduated', 'Secondary Graduation Year');
                $form->text('high_school_name', 'High School');
                $form->text('high_school_year_graduated', 'High School Graduation Year');
                $form->text('degree_university_name', 'University (Degree)');
                $form->text('degree_university_year_graduated', 'Degree Graduation Year');
                $form->text('masters_university_name', 'University (Masters)');
                $form->text('masters_university_year_graduated', 'Masters Graduation Year');
                $form->text('phd_university_name', 'University (PhD)');
                $form->text('phd_university_year_graduated', 'PhD Graduation Year');
            });

        $form->divider('SYSTEM ACCOUNT');
        
        $form->image('avatar', 'Profile Photo');

        if ($form->isCreating()) {
            $form->password('password', 'Password')->rules('required|confirmed|min:6');
            $form->password('password_confirmation', 'Confirm Password')->rules('required')
                ->default(function ($form) {
                    return $form->model()->password;
                });
        } else {
            $form->radio('change_password', 'Change Password')
                ->options([
                    'Change Password' => 'Change Password',
                    'Dont Change Password' => 'Don\'t Change Password'
                ])->when('Change Password', function ($form) {
                    $form->password('password', 'New Password')->rules('confirmed|min:6');
                    $form->password('password_confirmation', 'Confirm New Password')
                        ->default(function ($form) {
                            return $form->model()->password;
                        });
                });
        }

        $form->ignore(['password_confirmation', 'change_password', 'has_personal_info', 'has_family_info', 'add_financial_info', 'add_education_info', 'has_insurance']);
        
        $form->saving(function (Form $form) {
            $u = Admin::user();
            
            // Set enterprise_id to current user's enterprise
            if (!$form->enterprise_id) {
                $form->enterprise_id = $u->enterprise_id ?? 1;
            }
            
            // Set user_type to patient
            $form->user_type = 'patient';
            
            // Auto-generate name from first_name and last_name
            if ($form->first_name && $form->last_name) {
                $form->name = trim($form->first_name . ' ' . ($form->middle_name ? $form->middle_name . ' ' : '') . $form->last_name);
            }
            
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->disableReset();
        $form->disableViewCheck();
        return $form;
    }
}
