<?php

namespace App\Http\Controllers;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccountController extends BaseController
{

    public function register()
    {
        if (Auth::guard()->check()) {
            return redirect("/dashboard");
        }
        return view('register');
    }
    public function login()
    {
        if (Auth::guard()->check()) {
            return redirect("/dashboard");
        }
        return view('login');
    }


    public function account_details()
    {
        $_SESSION['form'] = Auth::user();
        return view('account-details');
    }

    public function dashboard()
    {
        return view('account-dashboard');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function login_post(Request $r)
    {


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





        if (Auth::attempt([
            'username' => $r->email,
            'password' => $r->password,
        ], true)) {
            return redirect('dashboard');
            die();
        }

        return back()
            ->withErrors(['password' => 'Wrong email or password.'])
            ->withInput();
    }

    public function account_details_post(Request $r)
    {

        if (Validator::make($_POST, [
            'username' => 'required|email',
        ])->fails()) {
            return back()
                ->withErrors(['email' => 'Enter a valid email address.'])
                ->withInput();
        }

        if (Validator::make($_POST, [
            'name' => 'required|min:2'
        ])->fails()) {
            return back()
                ->withErrors(['name' => 'Name is required.'])
                ->withInput();
        }





        return back()
            ->withErrors(['password' => 'Wrong email or password.'])
            ->withInput();
    }


    public function register_post(Request $r)
    {


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



        if (Auth::attempt([
            'username' => $r->email,
            'password' => $r->password,
        ], true)) {
            return redirect('dashboard');
            die();
        }
        return redirect('login');
    }
}
