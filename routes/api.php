<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiResurceController;
use App\Http\Controllers\ManifestController;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Middleware\JwtMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureTokenIsValid::class])->group(function () {});
Route::middleware([JwtMiddleware::class])->group(function () {
    // API routes protected by JWT middleware

    Route::get('users/me', [ApiAuthController::class, 'me']);
    Route::get('users', [ApiAuthController::class, 'users']);
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
    Route::POST("consultation-create", [ApiAuthController::class, 'consultation_create']);
    Route::POST("tasks-update-status", [ApiAuthController::class, 'tasks_update_status']);

    // Medical Services API routes
    Route::get('medical-services', [\App\Http\Controllers\Api\MedicalServiceController::class, 'index']);
    Route::post('medical-services', [\App\Http\Controllers\Api\MedicalServiceController::class, 'store']);
    Route::get('medical-services/{id}', [\App\Http\Controllers\Api\MedicalServiceController::class, 'show']);
    Route::put('medical-services/{id}', [\App\Http\Controllers\Api\MedicalServiceController::class, 'update']);
    Route::delete('medical-services/{id}', [\App\Http\Controllers\Api\MedicalServiceController::class, 'destroy']);
    Route::post('medical-services/{id}/items', [\App\Http\Controllers\Api\MedicalServiceController::class, 'addMedicalServiceItem']);
    Route::delete('medical-services/{serviceId}/items/{itemId}', [\App\Http\Controllers\Api\MedicalServiceController::class, 'deleteMedicalServiceItem']);

    // Specialized AJAX endpoints for dropdowns
    Route::get('ajax/consultations', [\App\Http\Controllers\Api\MedicalServiceController::class, 'getConsultationsForDropdown']);
    Route::get('ajax/employees', [\App\Http\Controllers\Api\MedicalServiceController::class, 'getEmployeesForDropdown']);
    Route::get('ajax/stock-items', [\App\Http\Controllers\Api\MedicalServiceController::class, 'getStockItemsForDropdown']);

    /**
     * Dynamic AJAX Provider for Dropdowns
     * 
     * This endpoint provides a secure, flexible way to fetch data for dynamic dropdowns
     * with search capabilities, filtering, and customizable formatting.
     * 
     * Query Parameters:
     * - model: The model name (required)
     * - q: Search query string
     * - search_by_1: Primary search field (default: 'name')
     * - search_by_2: Secondary search field (optional)
     * - display_format: How to format the display text
     * - limit: Number of results (max 50, default 20)
     * - query_*: Filter conditions (e.g., query_status=active)
     */
    Route::get('ajax', function (Request $request) {
        try {
            // Define allowed models for security
            $allowedModels = [
                'Patient', // Special case - handled as User with user_type = 'Patient'
                'Employee', 
                'User',
                'Doctor', // Special case - handled as User with user_type = 'Doctor'
                'Department',
                'Service',
                'Room',
                'Appointment',
                'Consultation',
                'Medicine',
                'Supplier',
                'Equipment',
                'Ward',
                'Bed',
                'StockItem'
            ];

            // Get and validate model parameter
            $modelName = trim($request->get('model', ''));
            if (empty($modelName) || !in_array($modelName, $allowedModels)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing model parameter',
                    'data' => []
                ], 400);
            }

            // Handle special cases for Patient, Doctor, and Employee
            $actualModelName = $modelName;
            $extraConditions = [];
            
            if ($modelName === 'Patient') {
                $actualModelName = 'User';
                $extraConditions['user_type'] = 'Patient';
            } elseif ($modelName === 'Doctor') {
                $actualModelName = 'User';
                $extraConditions['user_type'] = 'Doctor';
            } elseif ($modelName === 'Employee') {
                $actualModelName = 'User';
                // Get all users (employees) for the enterprise, no user_type filter
                // The enterprise_id will be automatically filtered by the logged-in user's context
            }

            // Build the full model class name
            $modelClass = "App\\Models\\{$actualModelName}";
            
            // Check if model class exists
            if (!class_exists($modelClass)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Model not found',
                    'data' => []
                ], 404);
            }

            // Get search parameters
            $searchQuery = trim($request->get('q', ''));
            $searchBy1 = trim($request->get('search_by_1', 'first_name'));
            $searchBy2 = trim($request->get('search_by_2', 'last_name'));
            $displayFormat = trim($request->get('display_format', 'full_name'));
            $limit = min(100, max(1, intval($request->get('limit', 100)))); // Max 100, min 1

            // Extract filter conditions from query_* parameters
            $conditions = [];
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'query_') === 0) {
                    $fieldName = str_replace('query_', '', $key);
                    // Sanitize field name to prevent injection
                    if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $fieldName)) {
                        // Skip 'role' field as it doesn't exist in User model
                        if ($fieldName !== 'role') {
                            $conditions[$fieldName] = $value;
                        }
                    }
                }
            }

            // Build the base query
            $query = $modelClass::query();

            // Add enterprise filtering for User-based models (automatic security)
            if ($actualModelName === 'User') {
                $user = auth()->user();
                if ($user && isset($user->enterprise_id)) {
                    $query->where('enterprise_id', $user->enterprise_id);
                }
            }

            // Apply special model conditions (like user_type for Patient/Doctor)
            foreach ($extraConditions as $field => $value) {
                $query->where($field, $value);
            }

            // Apply filter conditions from query_* parameters
            foreach ($conditions as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }

            $data = [];

            // Primary search
            if (!empty($searchQuery)) {
                $primaryQuery = clone $query;
                $primaryResults = $primaryQuery
                    ->where($searchBy1, 'LIKE', "%{$searchQuery}%")
                    ->limit($limit)
                    ->get();

                // Secondary search if needed and specified
                $secondaryResults = collect();
                if ($primaryResults->count() < $limit && !empty($searchBy2)) {
                    $secondaryQuery = clone $query;
                    $secondaryResults = $secondaryQuery
                        ->where($searchBy2, 'LIKE', "%{$searchQuery}%")
                        ->whereNotIn('id', $primaryResults->pluck('id')->toArray())
                        ->limit($limit - $primaryResults->count())
                        ->get();
                }

                $allResults = $primaryResults->merge($secondaryResults);
            } else {
                // No search query, just return filtered results
                $allResults = $query->limit($limit)->get();
            }

            // Format the results based on display_format
            foreach ($allResults as $item) {
                $formattedItem = formatDropdownItem($item, $displayFormat);
                if ($formattedItem) {
                    $data[] = $formattedItem;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => count($data),
                    'limit' => $limit,
                    'model' => $modelName,
                    'search_query' => $searchQuery,
                    'filters_applied' => count($conditions)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('AJAX Dropdown Error: ' . $e->getMessage(), [
                'model' => $request->get('model'),
                'query' => $request->get('q'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching data',
                'data' => []
            ], 500);
        }
    })->name('ajax.dropdown');

    // Dynamic API routes - support both create and update operations
    Route::get('api/{model}', [ApiResurceController::class, 'index']);
    Route::get('api/{model}/{id}', [ApiResurceController::class, 'show']);
    Route::post('api/{model}', [ApiResurceController::class, 'update']);

    // Manifest API - Complete application configuration for frontend
    Route::get('manifest', [ManifestController::class, 'index']);
    Route::post('manifest/clear-cache', [ManifestController::class, 'clearCache']);

});

Route::POST("users/login", [ApiAuthController::class, "login"]);
Route::POST("users/register", [ApiAuthController::class, "register"]);

// Public manifest endpoint for unauthenticated users
Route::get('manifest/public', [ManifestController::class, 'publicManifest']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Helper function to format dropdown items based on display format
 * Must be defined before the route that uses it
 */
if (!function_exists('formatDropdownItem')) {
    function formatDropdownItem($item, $format) {
        switch ($format) {
            case 'id_name':
                $text = "#{$item->id}";
                if (isset($item->name)) {
                    $text .= " - {$item->name}";
                }
                break;

            case 'name_only':
                $text = $item->name ?? "Item #{$item->id}";
                break;

            case 'full_name':
                if (isset($item->first_name) && isset($item->last_name)) {
                    $text = trim("{$item->first_name} {$item->last_name}");
                } elseif (isset($item->name)) {
                    $text = $item->name;
                } else {
                    $text = "#{$item->id}";
                }
                // Add additional info for patients/doctors
                if (isset($item->user_type)) {
                    if ($item->user_type === 'Patient' && isset($item->phone_number_1) && !empty($item->phone_number_1)) {
                        $text .= " - {$item->phone_number_1}";
                    } elseif ($item->user_type === 'Doctor' && isset($item->specialization) && !empty($item->specialization)) {
                        $text .= " ({$item->specialization})";
                    }
                }
                break;

            case 'email_name':
                $text = $item->email ?? "#{$item->id}";
                if (isset($item->name)) {
                    $text .= " ({$item->name})";
                }
                break;

            case 'consultation_patient':
                // Format: "CON-001 - John Doe" for consultations
                $consultationNumber = $item->consultation_number ?? "CON-{$item->id}";
                $patientName = $item->patient_name ?? 'Unknown Patient';
                $text = "{$consultationNumber} - {$patientName}";
                break;

            case 'stock_item_with_quantity':
                // Format: "Paracetamol (50 tablets)" for stock items
                $name = $item->name ?? "Item #{$item->id}";
                $quantity = $item->current_quantity ?? 0;
                $unit = $item->measuring_unit ?? 'units';
                $text = "{$name} ({$quantity} {$unit})";
                break;

            case 'custom':
                // For custom format, try to use a getDropdownText method on the model
                if (method_exists($item, 'getDropdownText')) {
                    $text = $item->getDropdownText();
                } else {
                    $text = $item->name ?? "#{$item->id}";
                }
                break;

            default:
                $text = "#{$item->id}";
                if (isset($item->name)) {
                    $text .= " - {$item->name}";
                }
                break;
        }

        return [
            'id' => $item->id,
            'text' => $text,
            'data' => [
                'model' => class_basename($item),
                'original' => $item->only([
                    'id', 'name', 'email', 'first_name', 'last_name', 'phone_number_1', 'user_type',
                    'consultation_number', 'patient_name', 'patient_id', 'main_status',
                    'current_quantity', 'measuring_unit', 'sale_price', 'specialization', 'department'
                ])
            ]
        ];
    }
}

// Legacy ajax-cards route (kept for backward compatibility)
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
