<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiResurceController;
use App\Http\Controllers\Api\MobileController;
use App\Http\Controllers\Api\OfflineSyncController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\MedicalServiceController;
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

// Mobile API Routes (enterprise-aware)
Route::prefix('mobile')->group(function () {
    Route::post('login', [MobileController::class, 'login']);
    Route::post('register', [MobileController::class, 'register']);

    // Protected routes (require authentication)
    Route::middleware('auth:api')->group(function () {
        // User profile
        Route::get('me', [MobileController::class, 'me']);
        Route::post('update-profile', [MobileController::class, 'updateProfile']);

        // Consultations
        Route::get('consultations', [MobileController::class, 'consultations']);
        Route::get('consultations/{id}', [MobileController::class, 'consultationDetails']);

        // Medical Services
        Route::get('medical-services', [MobileController::class, 'medicalServices']);
        Route::get('medical-services/{id}', [MobileController::class, 'medicalServiceDetails']);

        // Dashboard data
        Route::get('dashboard', [MobileController::class, 'dashboard']);

        // Push Notifications
        Route::post('device-tokens', [MobileController::class, 'registerDeviceToken']);
        Route::get('device-tokens', [MobileController::class, 'getUserDeviceTokens']);
        Route::post('device-tokens/deactivate', [MobileController::class, 'deactivateDeviceToken']);
        Route::post('notifications/test', [MobileController::class, 'sendTestNotification']);

        // Offline Synchronization
        Route::post('sync/to-server', [OfflineSyncController::class, 'syncToServer']);
        Route::get('sync/from-server', [OfflineSyncController::class, 'syncFromServer']);
        Route::get('sync/status', [OfflineSyncController::class, 'getSyncStatus']);
        Route::post('sync/resolve-conflicts', [OfflineSyncController::class, 'resolveConflicts']);
        Route::post('sync/queue', [OfflineSyncController::class, 'queueSync']);
        Route::post('sync/test', [OfflineSyncController::class, 'testSync']);

        // Location Services
        Route::post('location/record', [LocationController::class, 'recordLocation']);
        Route::get('location/nearby-facilities', [LocationController::class, 'findNearbyFacilities']);
        Route::post('location/emergency-share', [LocationController::class, 'shareEmergencyLocation']);
        Route::get('location/history', [LocationController::class, 'getLocationHistory']);
        Route::get('location/geofences', [LocationController::class, 'getGeofences']);
        Route::post('location/check-geofence', [LocationController::class, 'checkGeofence']);
        Route::get('location/travel-analytics', [LocationController::class, 'getTravelAnalytics']);
    });
});

// REST API v1 Routes (enterprise-aware)
Route::prefix('v1')->middleware(['api.security', 'api.rate_limit:120,1'])->group(function () {
    // Authentication routes (no auth middleware)
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

    // Protected routes (require JWT authentication)
    Route::middleware('auth:api')->group(function () {
        // Authentication management
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);
        Route::put('auth/profile', [AuthController::class, 'updateProfile']);

        // Patient management
        Route::prefix('patients')->middleware('api.rate_limit:60,1')->group(function () {
            Route::get('/', [PatientController::class, 'index']);
            Route::post('/', [PatientController::class, 'store']);
            Route::get('{patient}', [PatientController::class, 'show']);
            Route::put('{patient}', [PatientController::class, 'update']);
            Route::get('{patient}/consultations', [PatientController::class, 'consultations']);
            Route::get('{patient}/medical-records', [PatientController::class, 'medicalRecords']);
        });

        // Consultation management
        Route::prefix('consultations')->middleware('api.rate_limit:60,1')->group(function () {
            Route::get('/', [ConsultationController::class, 'index']);
            Route::post('/', [ConsultationController::class, 'store']);
            Route::get('{consultation}', [ConsultationController::class, 'show']);
            Route::put('{consultation}', [ConsultationController::class, 'update']);
            Route::post('{consultation}/medical-services', [ConsultationController::class, 'addMedicalServices']);
            Route::get('{consultation}/billing-summary', [ConsultationController::class, 'billingSummary']);
        });

        // Medical services management
        Route::prefix('medical-services')->middleware('api.rate_limit:60,1')->group(function () {
            // Service catalog
            Route::get('items', [MedicalServiceController::class, 'serviceItems']);
            Route::post('items', [MedicalServiceController::class, 'createServiceItem']);
            Route::get('items/{item}', [MedicalServiceController::class, 'serviceItem']);
            Route::put('items/{item}', [MedicalServiceController::class, 'updateServiceItem']);
            Route::get('categories', [MedicalServiceController::class, 'categories']);
            
            // Provided services
            Route::get('/', [MedicalServiceController::class, 'index']);
            Route::post('/', [MedicalServiceController::class, 'store']);
            Route::get('{service}', [MedicalServiceController::class, 'show']);
            Route::put('{service}', [MedicalServiceController::class, 'update']);
            Route::get('statistics', [MedicalServiceController::class, 'statistics']);
        });
    });
});


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
