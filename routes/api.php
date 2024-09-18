<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiResurceController;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware([EnsureTokenIsValid::class])->group(function () {});
Route::get('users/me', [ApiAuthController::class, 'me']);
Route::get('users', [ApiAuthController::class, 'users']);
Route::get('tasks', [ApiAuthController::class, 'tasks']);
Route::get('consultations', [ApiAuthController::class, 'consultations']);
Route::get('services', [ApiAuthController::class, 'services']);
Route::get('dose-item-records', [ApiAuthController::class, 'dose_item_records']);
Route::POST("dose-item-records-state", [ApiAuthController::class, 'dose_item_records_state']);

Route::POST("post-media-upload", [ApiAuthController::class, 'upload_media']);
Route::POST("update-profile", [ApiAuthController::class, 'update_profile']);
Route::POST("consultation-card-payment", [ApiAuthController::class, 'consultation_card_payment']);
Route::POST("consultation-flutterwave-payment", [ApiAuthController::class, 'consultation_flutterwave_payment']);
Route::POST("flutterwave-payment-verification", [ApiAuthController::class, 'flutterwave_payment_verification']);
Route::POST("delete-account", [ApiAuthController::class, 'delete_profile']);
Route::POST("password-change", [ApiAuthController::class, 'password_change']);
Route::POST("tasks-create", [ApiAuthController::class, 'tasks_create']);
Route::POST("consultation-create", [ApiAuthController::class, 'consultation_create']);
Route::POST("meetings", [ApiAuthController::class, 'meetings_create']);
Route::POST("tasks-update-status", [ApiAuthController::class, 'tasks_update_status']);
Route::POST("users/login", [ApiAuthController::class, "login"]);
Route::POST("users/register", [ApiAuthController::class, "register"]);

Route::get('api/{model}', [ApiResurceController::class, 'index']);
Route::post('api/{model}', [ApiResurceController::class, 'update']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('ajax', function (Request $r) {

    $_model = trim($r->get('model'));
    $conditions = [];
    foreach ($_GET as $key => $v) {
        if (substr($key, 0, 6) != 'query_') {
            continue;
        }
        $_key = str_replace('query_', "", $key);
        $conditions[$_key] = $v;
    }

    if (strlen($_model) < 2) {
        return [
            'data' => []
        ];
    }

    $model = "App\Models\\" . $_model;
    $search_by_1 = trim($r->get('search_by_1'));
    $search_by_2 = trim($r->get('search_by_2'));

    $q = trim($r->get('q'));

    $res_1 = $model::where(
        $search_by_1,
        'like',
        "%$q%"
    )
        ->where($conditions)
        ->limit(20)->get();
    $res_2 = [];

    if ((count($res_1) < 20) && (strlen($search_by_2) > 1)) {
        $res_2 = $model::where(
            $search_by_2,
            'like',
            "%$q%"
        )
            ->where($conditions)
            ->limit(20)->get();
    }

    $data = [];
    foreach ($res_1 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }
    foreach ($res_2 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }

    return [
        'data' => $data
    ];
});

Route::get('ajax-cards', function (Request $r) {

    $users = User::where('card_number', 'like', "%" . $r->get('q') . "%")
        ->limit(20)->get();
    $data = [];
    foreach ($users as $key => $v) {
        if ($v->card_status != "Active") {
            continue;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id - $v->card_number"
        ];
    }
    return [
        'data' => $data
    ];


    $_model = trim($r->get('model'));
    $conditions = [];
    foreach ($_GET as $key => $v) {
        if (substr($key, 0, 6) != 'query_') {
            continue;
        }
        $_key = str_replace('query_', "", $key);
        $conditions[$_key] = $v;
    }

    if (strlen($_model) < 2) {
        return [
            'data' => []
        ];
    }

    $model = "App\Models\\" . $_model;
    $search_by_1 = trim($r->get('search_by_1'));
    $search_by_2 = trim($r->get('search_by_2'));

    $q = trim($r->get('q'));

    $res_1 = $model::where(
        $search_by_1,
        'like',
        "%$q%"
    )
        ->where($conditions)
        ->limit(20)->get();
    $res_2 = [];

    if ((count($res_1) < 20) && (strlen($search_by_2) > 1)) {
        $res_2 = $model::where(
            $search_by_2,
            'like',
            "%$q%"
        )
            ->where($conditions)
            ->limit(20)->get();
    }

    $data = [];
    foreach ($res_1 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }
    foreach ($res_2 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }

    return [
        'data' => $data
    ];
});
