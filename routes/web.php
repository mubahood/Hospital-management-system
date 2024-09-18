<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\MainController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\Company;
use App\Models\Consultation;
use App\Models\Gen;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\ReportModel;
use App\Models\Task;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::get('migrate', function () {
    // Artisan::call('migrate');
    //do run laravel migration command
    Artisan::call('migrate', ['--force' => true]);
    //returning the output
    return Artisan::output();
});

Route::get('medical-report', function () {
    $id = $_GET['id'];
    $item = Consultation::find($id);
    if ($item == null) {
        die('item not found');
    }
    $item->process_invoice();

    if (isset($_GET['html'])) {
        return $item->process_report();
    }
    $item->process_report();
    $url = url('storage/' . $item->report_link);
    return redirect($url);
    die($url);;
});


Route::get('regenerate-invoice', function () {
    $id = $_GET['id'];
    $item = Consultation::find($id);
    if ($item == null) {
        die('item not found');
    }
    $item->process_invoice();
    $url = url('storage/' . $item->invoice_pdf);

    return redirect($url);
    die($url);
    $company = Company::find(1);
    $pdf = App::make('dompdf.wrapper');
    $pdf->set_option('enable_html5_parser', TRUE);
    $pdf->loadHTML(view('invoice', [
        'item' => $item,
        'company' => $company,
    ])->render());
    return $pdf->stream('test.pdf');
});



Route::get('app', function () {
    //return url('taskease-v1.apk');
    return redirect(url('taskease-v1.apk'));
});
Route::get('report', function () {

    $id = $_GET['id'];
    $item = ReportModel::find($id);
    $pdf = App::make('dompdf.wrapper');
    $pdf->set_option('enable_html5_parser', TRUE);
    $pdf->loadHTML(view('report', [
        'item' => $item,
    ])->render());
    return $pdf->stream('test.pdf');
});



Route::get('project-report', function () {

    $id = $_GET['id'];
    $project = Project::find($id);

    $pdf = App::make('dompdf.wrapper');
    //'isHtml5ParserEnabled', true
    $pdf->set_option('enable_html5_parser', TRUE);


    $pdf->loadHTML(view('project-report', [
        'title' => 'project',
        'item' => $project,
    ])->render());

    return $pdf->stream('file_name.pdf');
});

//return view('mail-1');

Route::get('reset-mail', function () {
    $u = User::find($_GET['id']);
    try {
        $u->send_password_reset();
        die('Email sent');
    } catch (\Throwable $th) {
        die($th->getMessage());
    }
});

Route::get('reset-password', function () {
    $u = User::where([
        'stream_id' => $_GET['token']
    ])->first();
    if ($u == null) {
        die('Invalid token');
    }
    return view('auth.reset-password', ['u' => $u]);
});

Route::post('reset-password', function () {
    $u = User::where([
        'stream_id' => $_GET['token']
    ])->first();
    if ($u == null) {
        die('Invalid token');
    }
    $p1 = $_POST['password'];
    $p2 = $_POST['password_1'];
    if ($p1 != $p2) {
        return back()
            ->withErrors(['password' => 'Passwords do not match.'])
            ->withInput();
    }
    $u->password = bcrypt($p1);
    $u->save();

    return redirect(admin_url('auth/login') . '?message=Password reset successful. Login to continue.');
    if (Auth::attempt([
        'email' => $u->email,
        'password' => $p1,
    ], true)) {
        die();
    }
    return back()
        ->withErrors(['password' => 'Failed to login. Try again.'])
        ->withInput();
});

Route::get('request-password-reset', function () {
    return view('auth.request-password-reset');
});

Route::post('request-password-reset', function (Request $r) {
    $u = User::where('email', $r->username)->first();
    if ($u == null) {
        return back()
            ->withErrors(['username' => 'Email address not found.'])
            ->withInput();
    }
    try {
        $u->send_password_reset();
        $msg = 'Password reset link has been sent to your email ' . $u->email . ".";
        return redirect(admin_url('auth/login') . '?message=' . $msg);
    } catch (\Throwable $th) {
        $msg = $th->getMessage();
        return back()
            ->withErrors(['username' => $msg])
            ->withInput();
    }
});

Route::get('auth/login', function () {
    $u = Admin::user();
    if ($u != null) {
        return redirect(url('/'));
    }

    return view('auth.login');
})->name('login');

Route::get('mobile', function () {
    return url('');
});
Route::get('test', function () {
    $m = Meeting::find(1);
});


Route::get('policy', function () {
    return view('policy');
});

Route::get('/gen-form', function () {
    die(Gen::find($_GET['id'])->make_forms());
})->name("gen-form");


Route::get('generate-class', [MainController::class, 'generate_class']);
Route::get('/gen', function () {
    $m = Gen::find($_GET['id']);
    if ($m == null) {
        return "Not found";
    }
    die($m->do_get());
})->name("register");
