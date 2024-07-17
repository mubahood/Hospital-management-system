<?php

namespace Encore\Admin\Controllers;

use App\Models\Campus;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @var string
     */
    protected $loginView = 'admin::login';

    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        //return redirect('register');
        return view($this->loginView);
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {

        if ($this->guard()->attempt([
            'email' => $request->username,
            'password' => $request->password,
        ], true)) {
            if ($this->guard()->attempt([
                'username' => $request->username,
                'password' => $request->password,
            ], true)) {
                if ($this->guard()->attempt([
                    'phone_number_1' => $request->username,
                    'password' => $request->password,
                ], true)) {
                    return $this->sendLoginResponse($request);
                }
            }
        }

        return back()
            ->withErrors(['password' => 'Wrong credentials.'])
            ->withInput();


        $r = $request;


        if (isset($_POST['password_1'])) {

            if (Validator::make($_POST, [
                'name' => 'required|string|min:4'
            ])->fails()) {
                return back()
                    ->withErrors(['name' => 'Enter your valid name.'])
                    ->withInput();
            }

            if (Validator::make($_POST, [
                'email' => 'required|email',
            ])->fails()) {
                return back()
                    ->withErrors(['email' => 'Enter a valid email address.'])
                    ->withInput();
            }

            if (Validator::make($_POST, [
                'password' => 'required|min:2'
            ])->fails()) {
                return back()
                    ->withErrors(['password' => 'Enter password with more than 3 chracters.'])
                    ->withInput();
            }

            if (Validator::make($_POST, [
                'password_1' => 'required|min:2'
            ])->fails()) {
                return back()
                    ->withErrors(['password_1' => 'Enter password with more than 3 chracters.'])
                    ->withInput();
            }

            if ($r->password != $r->password_1) {
                return back()
                    ->withErrors(['password_1' => 'Confirmation password did not match.'])
                    ->withInput();
            }

            $u = Administrator::where([
                'email' => $_POST['email']
            ])->orwhere([
                'username' => $_POST['email']
            ])->first();


            if ($u != null) {
                $u->username = $r->email;
                $u->email = $r->email;
                $u->password = password_hash($r->password, PASSWORD_DEFAULT);
                $u->save();
            } else {
                $admin = new Administrator();
                $admin->username = $r->email;
                $admin->name = $r->name;
                //$admin->avatar = 'user.png';
                $admin->password = password_hash($r->password, PASSWORD_DEFAULT);

                if (!$admin->save()) {
                    return back()
                        ->withErrors(['email' => 'Failed to create account. Try again.'])
                        ->withInput();
                }
            }
        }


        $u = Administrator::where([
            'email' => $_POST['email']
        ])->orwhere([
            'username' => $_POST['email']
        ])->first();


        if ($u == null) {
            return back()
                ->withErrors(['email' => 'Account with provided email address was not found.'])
                ->withInput();
        }


        $u->username = $r->email;
        $u->email = $r->email;
        $u->password = password_hash($r->password, PASSWORD_DEFAULT);
        $u->save();


        if (Auth::attempt([
            'email' => $r->email,
            'password' => $r->password,
        ], true)) {
        }


        $credentials = $request->only(['email', 'password']);
        $remember = true;

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        $credentials['username'] = $request->email;
        $credentials['password'] = $request->password;

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }


        return back()
            ->withErrors(['email' => 'Failed to log you in. Try again.'])
            ->withInput();
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }

    /**
     * User setting page.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function getSetting(Content $content)
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->disableView();
            }
        );

        return $content
            ->title('My profile')
            ->body($form->edit(Admin::user()->id));
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting()
    {
        return $this->settingForm()->update(Admin::user()->id);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());

        $form->divider('BIO DATA');

        $u = Admin::user();
        $form->hidden('company_id')->rules('required')->default($u->company_id)
            ->value($u->company_id);
        $form->text('first_name')->rules('required');
        $form->text('last_name')->rules('required');
        $form->date('date_of_birth');
        $form->text('place_of_birth');
        $form->radioCard('sex', 'Gender')->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');
        $form->text('phone_number_1', 'Mobile phone number')->rules('required');
        $form->text('phone_number_2', 'Home phone number');

        $form->divider('PERSONAL INFORMATION');

        $form->radioCard('has_personal_info', 'Does this user have personal information?')
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->when('Yes', function ($form) {
                $form->text('religion');
                $form->text('nationality');
                $form->text('home_address');
                $form->text('current_address');

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


        $form->divider('EDUCATIONAL INFORMATION');
        $form->radioCard('has_educational_info', 'Does this user have education information?')
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->when('Yes', function ($form) {

                $form->text('primary_school_name');
                $form->year('primary_school_year_graduated');
                $form->text('seconday_school_name');
                $form->year('seconday_school_year_graduated');
                $form->text('high_school_name');
                $form->year('high_school_year_graduated');

                $form->text('certificate_school_name');
                $form->year('certificate_year_graduated');

                $form->text('diploma_school_name');
                $form->year('diploma_year_graduated');

                $form->text('degree_university_name');
                $form->year('degree_university_year_graduated');
                $form->text('masters_university_name');
                $form->year('masters_university_year_graduated');
                $form->text('phd_university_name');
                $form->year('phd_university_year_graduated');
            });

        $form->divider('ACCOUNT NUMBERS');
        $form->radioCard('has_account_info', 'Does this user have account information?')
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->when('Yes', function ($form) {
                $form->text('national_id_number', 'National ID number');
                $form->text('passport_number', 'Passport number');
                $form->text('tin', 'TIN Number');
                $form->text('nssf_number', 'NSSF number');
                $form->text('bank_name');
                $form->text('bank_account_number');
            });

        $form->divider('SYSTEM ACCOUNT');
        $form->image('avatar', trans('admin.avatar'));

        $form->text('email', 'Email address')
            ->creationRules(["unique:admin_users"]);


        $form->radio('change_password', 'Do you want to change password?')->options(['No' => 'No', 'Yes' => 'Yes'])
            ->when('Yes', function ($form) {



                $form->password('password', trans('admin.password'))->rules('confirmed|required');
                $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
                    ->default(function ($form) {
                        return $form->model()->password;
                    });


                $form->ignore(['password_confirmation']);
                $form->ignore(['change_password']);
            })
            ->default('No');







        $form->setAction(admin_url('auth/setting'));
        $form->ignore(['password_confirmation']);
        $form->ignore(['change_password']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->disableReset();

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_url('auth/setting'));
        });

        return $form;
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }
}
