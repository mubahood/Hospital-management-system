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


Route::get('gen-dummy', function () {
    //set response to html
    /*   header('Content-Type: text/html');
    http_response_code(200);
    $faker = \Faker\Factory::create();
    $id = 1;
    $max = 200;
    for ($i = 0; $i < $max; $i++) {
        
        $user = new User();
        $user->enterprise_id = 1;
        $user->email = $faker->unique()->safeEmail();
        $user->username = $user->email;
        $user->name = $faker->name();
        $user->first_name = $faker->firstName();
        $user->last_name = $faker->lastName();
        $user->date_of_birth = $faker->date('Y-m-d', '2000-01-01');
        $user->place_of_birth = $faker->city();
        $user->sex = $faker->randomElement(['Male', 'Female']);
        $user->home_address = $faker->address();
        $user->current_address = $faker->address();
        $user->phone_number_1 = $faker->phoneNumber();
        $user->phone_number_2 = $faker->phoneNumber();
        $user->nationality = $faker->country();
        $user->religion = $faker->randomElement(['Christianity', 'Islam', 'Buddhism', 'Hinduism', 'Judaism']);
        $user->spouse_name = $faker->name();
        $user->spouse_phone = $faker->phoneNumber();
        $user->father_name = $faker->name('male');
        $user->father_phone = $faker->phoneNumber();
        $user->mother_name = $faker->name('female');
        $user->mother_phone = $faker->phoneNumber();
        $user->languages = $faker->randomElement(['English', 'Spanish', 'French', 'German', 'Chinese']);
        $user->emergency_person_name = $faker->name();
        $user->emergency_person_phone = $faker->phoneNumber();
        $user->national_id_number = $faker->randomNumber(8);
        $user->passport_number = $faker->randomNumber(6);
        $user->tin = $faker->randomNumber(6);
        $user->nssf_number = $faker->randomNumber(6);
        $user->bank_name = $faker->company();
        $user->bank_account_number = $faker->bankAccountNumber();
        $user->primary_school_name = $faker->company() . ' Primary School';
        $user->primary_school_year_graduated = $faker->year('2010');
        $user->seconday_school_name = $faker->company() . ' Secondary School';
        $user->seconday_school_year_graduated = $faker->year('2015');
        $user->high_school_name = $faker->company() . ' High School';
        $user->high_school_year_graduated = $faker->year('2018');
        $user->degree_university_name = $faker->company() . ' University';
        $user->degree_university_year_graduated = $faker->year('2022');
        $user->masters_university_name = $faker->company() . ' University';
        $user->masters_university_year_graduated = $faker->year('2024');
        $user->phd_university_name = $faker->company() . ' University';
        $user->phd_university_year_graduated = $faker->year('2027');
        $user->diploma_school_name = $faker->company() . ' Institute';
        $user->diploma_year_graduated = $faker->year('2020');
        $user->certificate_school_name = $faker->company() . ' Center';
        $user->certificate_year_graduated = $faker->year('2019');
        $user->marital_status = $faker->randomElement(['Single', 'Married', 'Divorced', 'Widowed']);
        $user->title = $faker->title();
        // $user->intro = $faker->paragraph();
        $user->rate = $faker->numberBetween(1, 10);

    "id" => 2
    "username" => "awisozk@example.net"
    "password" => "$2y$10$pm2xXpbYc1ohQJNLO/4H2upfEhGKsBTCtfFjFgmyokWX4r7ijgNa2"
    "name" => "8tech Admin"
    "avatar" => "images/098eb9e1fddf929c82214141636b1f4b.png"
    "remember_token" => "hA0GnLwvlTmhXhvEOWFBy7ritdN9BXe8gJRnrVtJJnfLQCRv6ncy1AEogYqr"
    "created_at" => "2023-09-27 17:02:28"
    "updated_at" => "2024-08-03 21:41:44"
    "enterprise_id" => 1
    "first_name" => "8tech"
    "last_name" => "Admin"
    "date_of_birth" => "2023-09-27"
    "place_of_birth" => "Consequat Voluptati"
    "sex" => "Female"
    "home_address" => "Qui vitae unde eum q"
    "current_address" => "Aut voluptate anim v"
    "phone_number_1" => "+1 (437) 915-8734"
    "phone_number_2" => "+1 (513) 549-7409"
    "email" => "awisozk@example.net"
    "nationality" => "Quo architecto eveni"
    "religion" => "Fugit molestiae nul"
    "spouse_name" => "Cheyenne Velasquez"
    "spouse_phone" => "+1 (637) 864-5592"
    "father_name" => "Naida Vance"
    "father_phone" => "+1 (946) 869-4363"
    "mother_name" => "Clark Cobb"
    "mother_phone" => "+1 (886) 997-4466"
    "languages" => "Quis ex commodo saep"
    "emergency_person_name" => "Nolan Moses"
    "emergency_person_phone" => "+1 (455) 477-2441"
    "national_id_number" => "599"
    "passport_number" => "397"
    "tin" => "253"
    "nssf_number" => "127"
    "bank_name" => "Kamal Acevedo"
    "bank_account_number" => "1"
    "primary_school_name" => "Odessa Vincent"
    "primary_school_year_graduated" => "2006"
    "seconday_school_name" => "Calvin Gardner"
    "seconday_school_year_graduated" => "2014"
    "high_school_name" => "Lee Ratliff"
    "high_school_year_graduated" => "1991"
    "degree_university_name" => "Nigel Valencia"
    "degree_university_year_graduated" => "2018"
    "masters_university_name" => "Chaney Ortega"
    "masters_university_year_graduated" => "1989"
    "phd_university_name" => "Lacey Wolf"
    "phd_university_year_graduated" => "1989"
    "user_type" => "employee"
    "demo_id" => 0
    "user_id" => null
    "user_batch_importer_id" => 0
    "school_pay_account_id" => null
    "school_pay_payment_code" => null
    "given_name" => null
    "deleted_at" => null
    "marital_status" => null
    "verification" => 0
    "current_class_id" => 0
    "current_theology_class_id" => 0
    "status" => 2
    "parent_id" => null
    "main_role_id" => null
    "stream_id" => null
    "account_id" => null
    "has_personal_info" => "Yes"
    "has_educational_info" => "Yes"
    "has_account_info" => "Yes"
    "diploma_school_name" => "Colby Chaney"
    "diploma_year_graduated" => "1976"
    "certificate_school_name" => "Alisa Watts"
    "certificate_year_graduated" => "1987"
    "company_id" => 2
    "managed_by" => null
    "title" => null
    "dob" => null
    "intro" => null
    "rate" => 6
    "can_evaluate" => "No"
    "work_load_pending" => 0
    "work_load_completed" => 4
    "belongs_to_company" => null
    "card_status" => "Pending"
    "card_number" => null
    "card_balance" => null
    "card_accepts_credit" => "0"
    "card_max_credit" => null
    "card_accepts_cash" => 0
    "is_dependent" => "0"
    "dependent_status" => "Pending"
    "dependent_id" => null
    "card_expiry" => null
    "belongs_to_company_status" => "Pending"


        $user->save();
        echo "Updating user {$user->id}. {$user->email} - {$user->name}<br/>";
    }
    die('Done');*/
});
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
