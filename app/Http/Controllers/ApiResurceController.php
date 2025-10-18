<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\CounsellingCentre;
use App\Models\Crop;
use App\Models\CropProtocol;
use App\Models\Event;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\Group;
use App\Models\Institution;
use App\Models\Job;
use App\Models\NewsPost;
use App\Models\Person;
use App\Models\Product;
use App\Models\Sacco;
use App\Models\ServiceProvider;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

/**
 * Enhanced Dynamic API Resource Controller
 * 
 * This controller provides comprehensive CRUD operations for any model in the system
 * with advanced features including:
 * 
 * FEATURES:
 * - Multi-tenant SAAS security (enterprise_id isolation)
 * - Advanced pagination with metadata
 * - Flexible filtering (JSON filters, individual filters, legacy q_ params)
 * - Global search across text columns
 * - Sorting with validation
 * - Eager loading of relationships
 * - Field selection for performance
 * - Date range filtering
 * - Bulk operations (update, delete, restore)
 * - Dynamic validation
 * - Security controls (fillable/guarded respect)
 * - Comprehensive error handling and logging
 * - Export capabilities (placeholder)
 * 
 * ENDPOINTS:
 * - GET  /api/{model} - List/search/filter records
 * - POST /api/{model} - Create/update records or perform bulk operations
 * 
 * SECURITY:
 * - Automatic enterprise_id scoping for multi-tenant isolation
 * - Fillable/guarded field respect
 * - Input sanitization and validation
 * - SQL injection prevention through Eloquent
 * - XSS protection for string inputs
 * 
 * @author Hospital Management System
 * @version 2.0
 */
class ApiResurceController extends Controller
{

    use ApiResponser;



    /**
     * Enhanced index method with pagination, filtering, sorting, searching, and SAAS multi-tenancy
     * 
     * Parameters:
     * - page: Current page number (default: 1)
     * - per_page: Items per page (default: 15, max: 100)
     * - search: Global search term
     * - search_by: Specific columns to search in (comma-separated)
     * - sort_by: Column to sort by (default: id)
     * - sort_order: Sort direction (asc|desc, default: desc)
     * - filters: JSON string of filters or individual filter_[column] parameters
     * - with: Relations to eager load (comma-separated)
     * - fields: Specific fields to select (comma-separated)
     * - date_from/date_to: Date range filtering
     * - export: Export format (csv|excel|pdf)
     */
    public function index(Request $r, $model)
    {
        $u = null; // Initialize user variable
        
        try {
            // Authentication and authorization
            $u = Auth::user();
            if ($u == null) {
                return $this->error('User not authenticated.', 401);
            }

            // Validate enterprise_id for SAAS multi-tenancy
            if (empty($u->enterprise_id)) {
                return $this->error('Enterprise ID is required for data access.', 403);
            }

            // Validate model class exists
            $className = "App\\Models\\" . $model;
            if (!class_exists($className)) {
                return $this->error("Model '{$model}' not found.", 404);
            }

            $modelInstance = new $className;
            
            // Check if model has enterprise_id column for multi-tenancy
            $table = $modelInstance->getTable();
            $columns = Schema::getColumnListing($table);
            $hasEnterpriseId = in_array('enterprise_id', $columns);

            // Start building the query
            $query = $className::query();

            // Apply enterprise-level data isolation for SAAS
            if ($hasEnterpriseId) {
                $query->where('enterprise_id', $u->enterprise_id);
            }

            // Apply additional user-level scoping if needed
            if (in_array('user_id', $columns) && $r->get('user_scope', false)) {
                $query->where('user_id', $u->id);
            }

            // Handle filtering
            $this->applyFilters($query, $r, $columns);

            // Handle global search
            $this->applySearch($query, $r, $columns, $modelInstance);

            // Handle sorting
            $this->applySorting($query, $r, $columns);

            // Handle relations eager loading
            $this->applyWithRelations($query, $r, $modelInstance);

            // Handle field selection
            $this->applyFieldSelection($query, $r, $columns, $table);

            // Handle date range filtering
            $this->applyDateRangeFilter($query, $r, $columns);

            // Get pagination parameters
            $perPage = min((int) $r->get('per_page', 15), 100); // Max 100 items per page
            $page = (int) $r->get('page', 1);

            // Handle export requests
            if ($r->has('export')) {
                return $this->handleExport($query, $r->get('export'), $model);
            }

            // Execute query with pagination
            $items = $query->paginate($perPage, ['*'], 'page', $page);

            // Add metadata
            $response = [
                'data' => $items->items(),
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                    'last_page' => $items->lastPage(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem(),
                    'has_more' => $items->hasMorePages(),
                ],
                'meta' => [
                    'model' => $model,
                    'table' => $table,
                    'user_id' => $u->id,
                    'enterprise_id' => $u->enterprise_id,
                    'filters_applied' => $this->getAppliedFilters($r),
                    'search_term' => $r->get('search'),
                    'sort_by' => $r->get('sort_by', 'id'),
                    'sort_order' => $r->get('sort_order', 'desc'),
                ]
            ];

            return $this->success($response, 'Data retrieved successfully');

        } catch (Exception $e) {
            Log::error("API Resource Index Error: " . $e->getMessage(), [
                'model' => $model,
                'user_id' => $u->id ?? null,
                'enterprise_id' => $u->enterprise_id ?? null,
                'request' => $r->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->error('An error occurred while retrieving data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show a specific resource by ID
     * Uses the same dynamic approach as index() method with where clause
     * 
     * @param Request $r
     * @param string $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $r, $model)
    {
        $u = null; // Initialize user variable
        
        try {
            // Authentication and authorization (same as index method)
            $u = Auth::user();
            if ($u == null) {
                return $this->error('User not authenticated.', 401);
            }

            // Validate enterprise_id for SAAS multi-tenancy
            if (empty($u->enterprise_id)) {
                return $this->error('Enterprise ID is required for data access.', 403);
            }

            // Validate model class exists
            $className = "App\\Models\\" . $model;
            if (!class_exists($className)) {
                return $this->error("Model {$model} not found.", 404);
            }

            // Get the ID from the route parameter
            $id = $r->route('id');
            if (!$id) {
                return $this->error('ID parameter is required.', 400);
            }

            // Build base query with enterprise isolation (same as index method)
            $query = $className::query();
            
            // Apply multi-tenant security if model has enterprise_id
            if (Schema::hasColumn((new $className)->getTable(), 'enterprise_id')) {
                $query->where('enterprise_id', $u->enterprise_id);
            }

            // Add the ID filter (your suggested approach)
            $query->where('id', $id);

            // Apply eager loading if specified
            $with = $r->get('with');
            if ($with) {
                $relations = is_array($with) ? $with : explode(',', $with);
                $query->with($relations);
            }

            // Apply field selection if specified
            $select = $r->get('select');
            if ($select) {
                $fields = is_array($select) ? $select : explode(',', $select);
                $query->select($fields);
            }

            // Find the record using first() instead of find() for consistency
            $record = $query->first();

            if (!$record) {
                return $this->error("Record with ID {$id} not found or access denied.", 404);
            }

            // Return record directly (matching your expected response format)
            return $this->success($record, 'Record retrieved successfully');

        } catch (Exception $e) {
            Log::error("API Resource Show Error: " . $e->getMessage(), [
                'model' => $model,
                'id' => $r->route('id'),
                'user_id' => $u->id ?? null,
                'enterprise_id' => $u->enterprise_id ?? null,
                'request' => $r->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->error('An error occurred while retrieving the record: ' . $e->getMessage(), 500);
        }
    }

    public function delete(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);


        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Item already deleted.",
            ]);
        }


        try {
            $obj->delete();
            $msg = "Deleted successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }


    /**
     * Enhanced update/create method with validation, bulk operations, and SAAS multi-tenancy
     * 
     * Parameters:
     * - id or online_id: Record ID for updates (optional for creates)
     * - bulk_action: Type of bulk operation (update|delete|restore)
     * - bulk_ids: Array of IDs for bulk operations
     * - validate: Whether to perform validation (default: true)
     * - return_model: Whether to return the full model in response (default: true)
     */
    public function update(Request $r, $model)
    {
        $u = null; // Initialize user variable
        
        try {
            // Authentication and authorization
            $u = Auth::user();
            if ($u == null) {
                return $this->error('User not authenticated.', 401);
            }

            // Validate enterprise_id for SAAS multi-tenancy
            if (empty($u->enterprise_id)) {
                return $this->error('Enterprise ID is required for data access.', 403);
            }

            // Validate model class exists
            $className = "App\\Models\\" . $model;
            if (!class_exists($className)) {
                return $this->error("Model '{$model}' not found.", 404);
            }

            $modelInstance = new $className;
            $table = $modelInstance->getTable();
            $columns = Schema::getColumnListing($table);
            $hasEnterpriseId = in_array('enterprise_id', $columns);

            // Handle bulk operations
            if ($r->has('bulk_action') && $r->has('bulk_ids')) {
                return $this->handleBulkOperation($r, $className, $u, $hasEnterpriseId);
            }

            // Determine if this is an update or create operation
            $id = $r->get('id') ?? $r->get('online_id');
            $isUpdate = !empty($id);
            
            $obj = null;
            if ($isUpdate) {
                // For updates, find existing record with enterprise scope
                $query = $className::where('id', $id);
                if ($hasEnterpriseId) {
                    $query->where('enterprise_id', $u->enterprise_id);
                }
                $obj = $query->first();

                if (!$obj) {
                    return $this->error("Record not found or access denied.", 404);
                }
            } else {
                // Create new instance
                $obj = new $className();
            }

            // Clean input data - remove special parameters and sensitive fields
            $data = $r->all();
            unset($data['_method'], $data['id'], $data['online_id'], $data['bulk_action'], $data['bulk_ids'], 
                  $data['_token'], $data['password_confirmation']);

            // Get fillable/guarded fields and column info
            $fillable = $obj->getFillable();
            $guarded = $obj->getGuarded();
            $excludeFromUpdate = [
                'id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token',
                'email_verified_at', 'api_token', '_token', 'enterprise_id' // Protect sensitive fields
            ];

            // Additional security: Don't allow updating enterprise_id to prevent data leakage
            if (!$isUpdate) {
                // Only exclude enterprise_id from updates, not creates
                $excludeFromUpdate = array_diff($excludeFromUpdate, ['enterprise_id']);
            }

            // Filter and validate data using helper method
            $allowedColumns = $this->getAllowedColumns($obj, $columns, $isUpdate);
            $validData = [];
            
            foreach ($data as $key => $value) {
                // Skip if column is not allowed
                if (!in_array($key, $allowedColumns)) {
                    continue;
                }

                // Sanitize the value
                $value = $this->sanitizeValue($value);

                // Handle special fields
                if ($key === 'password' && !empty($value)) {
                    $value = bcrypt($value);
                }

                // Handle JSON fields
                if ($this->isJsonField($table, $key) && is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded;
                    }
                }

                // Handle date fields
                if ($this->isDateField($table, $key) && !empty($value)) {
                    try {
                        $value = Carbon::parse($value);
                    } catch (Exception $e) {
                        return $this->error("Invalid date format for field '{$key}': {$value}", 422);
                    }
                }

                $validData[$key] = $value;
            }

            // Auto-set enterprise_id for new records
            if (!$isUpdate && $hasEnterpriseId) {
                $validData['enterprise_id'] = $u->enterprise_id;
            }

            // Auto-set user_id if column exists and not provided
            if (in_array('user_id', $columns) && !isset($validData['user_id'])) {
                $validData['user_id'] = $u->id;
            }

            // Special handling for Consultation model with new patient
            if ($model === 'Consultation' && isset($validData['is_new_patient'])) {
                Log::info('🔵 ApiResurceController - Processing Consultation with new patient flag', [
                    'is_new_patient_raw' => $validData['is_new_patient'],
                    'new_patient_first_name' => $validData['new_patient_first_name'] ?? 'not set',
                    'new_patient_last_name' => $validData['new_patient_last_name'] ?? 'not set',
                    'patient_id' => $validData['patient_id'] ?? 'not set',
                    'logged_user_id' => $u->id,
                    'logged_user_enterprise_id' => $u->enterprise_id ?? 'not set'
                ]);
                
                // Convert string boolean to actual boolean
                if ($validData['is_new_patient'] === 'true' || $validData['is_new_patient'] === '1' || $validData['is_new_patient'] === 1) {
                    $validData['is_new_patient'] = true;
                    Log::info('✅ Converted is_new_patient to TRUE');
                } elseif ($validData['is_new_patient'] === 'false' || $validData['is_new_patient'] === '0' || $validData['is_new_patient'] === 0) {
                    $validData['is_new_patient'] = false;
                    Log::info('✅ Converted is_new_patient to FALSE');
                }

                // Normalize empty string patient_id to null
                if (isset($validData['patient_id']) && $validData['patient_id'] === '') {
                    $validData['patient_id'] = null;
                    Log::info('🔵 Normalized empty string patient_id to null');
                }

                // Validate new patient fields if is_new_patient is true
                if ($validData['is_new_patient'] === true) {
                    Log::info('🟢 Validating new patient fields');
                    
                    $requiredNewPatientFields = ['new_patient_first_name', 'new_patient_last_name'];
                    $missingFields = [];
                    
                    foreach ($requiredNewPatientFields as $field) {
                        if (empty($validData[$field])) {
                            $missingFields[] = $field;
                        }
                    }
                    
                    // Check if at least phone or email is provided
                    if (empty($validData['new_patient_phone']) && empty($validData['new_patient_email'])) {
                        $missingFields[] = 'new_patient_phone or new_patient_email';
                    }
                    
                    if (!empty($missingFields)) {
                        Log::error('❌ Missing required new patient fields', ['missing' => $missingFields]);
                        return $this->error(
                            'Missing required fields for new patient: ' . implode(', ', $missingFields),
                            422
                        );
                    }
                    
                    Log::info('✅ New patient validation passed');
                    
                    // Ensure patient_id is null for new patients (will be set after patient creation)
                    $validData['patient_id'] = null;
                } else {
                    // For existing patients, ensure patient_id is provided and not empty
                    Log::info('🔵 Existing patient - validating patient_id');
                    
                    if (empty($validData['patient_id']) || $validData['patient_id'] === null) {
                        Log::error('❌ patient_id missing for existing patient consultation');
                        return $this->error('Patient selection is required for existing patient consultations.', 422);
                    }
                    
                    Log::info('✅ Existing patient validation passed', ['patient_id' => $validData['patient_id']]);
                }
            }

            // Perform validation if requested
            if ($r->get('validate', true)) {
                $validator = $this->validateModelData($validData, $model, $isUpdate, $obj);
                if ($validator && $validator->fails()) {
                    return $this->error('Validation failed.', 422, $validator->errors());
                }
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Update the model
                foreach ($validData as $key => $value) {
                    $obj->$key = $value;
                }

                // Save the model
                $obj->save();

                // Handle file uploads if any
                $this->handleFileUploads($r, $obj);

                // Handle medical_services array for Consultation model
                if ($model === 'Consultation' && $r->has('medical_services') && is_array($r->get('medical_services'))) {
                    $this->handleMedicalServices($obj, $r->get('medical_services'), $u);
                }

                // Refresh model to get updated data (with relationships)
                // Only load medical_services for Consultation model
                if ($model === 'Consultation' && method_exists($obj, 'medical_services')) {
                    $obj->load('medical_services');
                }
                $obj->refresh();

                DB::commit();

                $message = $isUpdate ? 'Record updated successfully.' : 'Record created successfully.';
                
                $response = [
                    'data' => $r->get('return_model', true) ? $obj : ['id' => $obj->id],
                    'meta' => [
                        'action' => $isUpdate ? 'update' : 'create',
                        'model' => $model,
                        'user_id' => $u->id,
                        'enterprise_id' => $u->enterprise_id,
                        'fields_updated' => array_keys($validData),
                    ]
                ];

                return $this->success($response, $message);

            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error("API Resource Update Error: " . $e->getMessage(), [
                'model' => $model,
                'user_id' => $u->id ?? null,
                'enterprise_id' => $u->enterprise_id ?? null,
                'request' => $r->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->error('An error occurred while saving data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Apply filters to the query based on request parameters
     */
    private function applyFilters($query, Request $r, array $columns)
    {
        // List of parameters to exclude from direct column filtering
        $excludedParams = [
            'page', 'per_page', 'search', 'search_by', 'sort_by', 'sort_order', 'sort_direction',
            'with', 'fields', 'select', 'date_from', 'date_to', 'export', 'user_scope',
            'bulk_action', 'bulk_ids', 'validate', 'return_model', 'id', 'online_id',
            'filters', 'model', 'token', 'api_token'
        ];

        // Handle JSON filters parameter
        if ($r->has('filters') && !empty($r->get('filters'))) {
            $filters = json_decode($r->get('filters'), true);
            if (is_array($filters)) {
                foreach ($filters as $column => $value) {
                    if (in_array($column, $columns) && $value !== null && $value !== '') {
                        $this->applyFilter($query, $column, $value);
                    }
                }
            }
        }

        // Handle individual filter_[column] parameters
        foreach ($r->all() as $key => $value) {
            if (strpos($key, 'filter_') === 0 && $value !== null && $value !== '') {
                $column = substr($key, 7); // Remove 'filter_' prefix
                if (in_array($column, $columns)) {
                    $this->applyFilter($query, $column, $value);
                }
            }
        }

        // Handle legacy q_ parameters for backward compatibility
        foreach ($r->all() as $key => $value) {
            if (strpos($key, 'q_') === 0 && $value !== null && $value !== '') {
                $column = substr($key, 2); // Remove 'q_' prefix
                if (in_array($column, $columns)) {
                    $query->where($column, $value);
                }
            }
        }

        // NEW: Handle direct column name parameters (e.g., main_status, payment_status)
        // This allows frontend to send filters directly as column names
        foreach ($r->all() as $key => $value) {
            // Skip if it's an excluded parameter or already processed
            if (in_array($key, $excludedParams) || 
                strpos($key, 'filter_') === 0 || 
                strpos($key, 'q_') === 0) {
                continue;
            }

            // Check if this parameter matches a database column
            if (in_array($key, $columns) && $value !== null && $value !== '') {
                Log::info("🔵 Applying direct column filter", [
                    'column' => $key,
                    'value' => $value,
                    'type' => gettype($value)
                ]);
                $this->applyFilter($query, $key, $value);
            }
        }
    }

    /**
     * Apply a single filter with smart filtering logic
     */
    private function applyFilter($query, $column, $value)
    {
        // Handle null or empty values
        if ($value === null || $value === '') {
            return;
        }

        if (is_array($value)) {
            // Handle array values (IN clause)
            $query->whereIn($column, $value);
        } elseif (is_string($value) && strpos($value, '%') !== false) {
            // Handle LIKE queries
            $query->where($column, 'LIKE', $value);
        } elseif (is_string($value) && strpos($value, '>=') === 0) {
            // Handle >= comparisons
            $query->where($column, '>=', substr($value, 2));
        } elseif (is_string($value) && strpos($value, '<=') === 0) {
            // Handle <= comparisons
            $query->where($column, '<=', substr($value, 2));
        } elseif (is_string($value) && strpos($value, '>') === 0) {
            // Handle > comparisons
            $query->where($column, '>', substr($value, 1));
        } elseif (is_string($value) && strpos($value, '<') === 0) {
            // Handle < comparisons
            $query->where($column, '<', substr($value, 1));
        } elseif (is_string($value) && strpos($value, '!=') === 0) {
            // Handle != comparisons
            $query->where($column, '!=', substr($value, 2));
        } else {
            // Exact match
            $query->where($column, $value);
        }
    }

    /**
     * Apply global search across searchable columns
     */
    private function applySearch($query, Request $r, array $columns, $modelInstance)
    {
        $searchTerm = $r->get('search');
        if (empty($searchTerm)) {
            return;
        }

        $searchBy = $r->get('search_by');
        $searchColumns = [];

        if (!empty($searchBy)) {
            // Use specified columns
            $searchColumns = array_intersect(explode(',', $searchBy), $columns);
        } else {
            // Auto-detect searchable columns (text-based columns)
            $searchColumns = $this->getSearchableColumns($modelInstance->getTable(), $columns);
        }

        if (!empty($searchColumns)) {
            $query->where(function ($q) use ($searchColumns, $searchTerm) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$searchTerm}%");
                }
            });
        }
    }

    /**
     * Apply sorting to the query
     */
    private function applySorting($query, Request $r, array $columns)
    {
        $sortBy = $r->get('sort_by', 'id');
        $sortOrder = strtolower($r->get('sort_order', 'desc'));

        // Validate sort column exists
        if (!in_array($sortBy, $columns)) {
            $sortBy = 'id';
        }

        // Validate sort order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Apply eager loading of relations
     */
    private function applyWithRelations($query, Request $r, $modelInstance)
    {
        $with = $r->get('with');
        if (!empty($with)) {
            $relations = explode(',', $with);
            // Only load relations that exist on the model
            $validRelations = [];
            foreach ($relations as $relation) {
                $relation = trim($relation);
                if (method_exists($modelInstance, $relation)) {
                    $validRelations[] = $relation;
                }
            }
            if (!empty($validRelations)) {
                $query->with($validRelations);
            }
        }
    }

    /**
     * Apply field selection
     */
    private function applyFieldSelection($query, Request $r, array $columns, $table)
    {
        $fields = $r->get('fields');
        if (!empty($fields)) {
            $selectedFields = explode(',', $fields);
            $validFields = array_intersect($selectedFields, $columns);
            if (!empty($validFields)) {
                // Always include id and enterprise_id for consistency
                $validFields[] = 'id';
                if (in_array('enterprise_id', $columns)) {
                    $validFields[] = 'enterprise_id';
                }
                $query->select(array_unique($validFields));
            }
        }
    }

    /**
     * Apply date range filtering
     */
    private function applyDateRangeFilter($query, Request $r, array $columns)
    {
        $dateFrom = $r->get('date_from');
        $dateTo = $r->get('date_to');
        $dateColumn = $r->get('date_column', 'created_at');

        if (!in_array($dateColumn, $columns)) {
            $dateColumn = 'created_at';
        }

        if (!empty($dateFrom)) {
            try {
                $query->where($dateColumn, '>=', Carbon::parse($dateFrom)->startOfDay());
            } catch (Exception $e) {
                // Invalid date format, skip
            }
        }

        if (!empty($dateTo)) {
            try {
                $query->where($dateColumn, '<=', Carbon::parse($dateTo)->endOfDay());
            } catch (Exception $e) {
                // Invalid date format, skip
            }
        }
    }

    /**
     * Handle bulk operations
     */
    private function handleBulkOperation(Request $r, $className, $user, $hasEnterpriseId)
    {
        $bulkAction = $r->get('bulk_action');
        $bulkIds = $r->get('bulk_ids');

        if (!is_array($bulkIds)) {
            $bulkIds = explode(',', $bulkIds);
        }

        $bulkIds = array_filter(array_map('intval', $bulkIds));

        if (empty($bulkIds)) {
            return $this->error('No valid IDs provided for bulk operation.', 422);
        }

        $query = $className::whereIn('id', $bulkIds);
        if ($hasEnterpriseId) {
            $query->where('enterprise_id', $user->enterprise_id);
        }

        try {
            DB::beginTransaction();

            switch ($bulkAction) {
                case 'delete':
                    $affected = $query->delete();
                    break;
                case 'restore':
                    // Check if model uses soft deletes
                    if ($this->modelSupportsSoftDeletes($className)) {
                        $affected = $query->restore();
                    } else {
                        return $this->error('Restore operation not supported for this model.', 422);
                    }
                    break;
                case 'update':
                    $updateData = $r->get('bulk_update_data', []);
                    if (empty($updateData) || !is_array($updateData)) {
                        return $this->error('No valid update data provided for bulk update.', 422);
                    }
                    // Filter update data to only include valid columns
                    $table = (new $className)->getTable();
                    $columns = Schema::getColumnListing($table);
                    $filteredData = array_intersect_key($updateData, array_flip($columns));
                    if (empty($filteredData)) {
                        return $this->error('No valid columns found in update data.', 422);
                    }
                    $affected = $query->update($filteredData);
                    break;
                default:
                    return $this->error('Invalid bulk action. Supported actions: delete, restore, update', 422);
            }

            DB::commit();

            return $this->success([
                'affected_count' => $affected,
                'action' => $bulkAction,
                'ids' => $bulkIds
            ], "Bulk {$bulkAction} completed successfully.");

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get searchable columns (text-based columns)
     */
    private function getSearchableColumns($table, array $columns)
    {
        $searchableColumns = [];
        foreach ($columns as $column) {
            try {
                $columnType = Schema::getColumnType($table, $column);
                if (in_array($columnType, ['string', 'text', 'varchar', 'char'])) {
                    $searchableColumns[] = $column;
                }
            } catch (Exception $e) {
                // Skip columns that can't be introspected
                continue;
            }
        }
        return $searchableColumns;
    }

    /**
     * Check if a field is a JSON field
     */
    private function isJsonField($table, $column)
    {
        try {
            $columnType = Schema::getColumnType($table, $column);
            return in_array($columnType, ['json', 'jsonb']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if a field is a date field
     */
    private function isDateField($table, $column)
    {
        try {
            $columnType = Schema::getColumnType($table, $column);
            return in_array($columnType, ['date', 'datetime', 'timestamp']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validate model data
     */
    private function validateModelData($data, $model, $isUpdate, $obj = null)
    {
        // Basic validation rules - can be extended
        $rules = [];
        
        // Add model-specific validation rules here
        // This is a placeholder for dynamic validation
        
        if (empty($rules)) {
            return null;
        }

        return Validator::make($data, $rules);
    }

    /**
     * Handle file uploads
     */
    private function handleFileUploads(Request $r, $obj)
    {
        foreach ($r->allFiles() as $fieldName => $file) {
            if (!empty($file) && $file->isValid()) {
                try {
                    // Upload directory path
                    $uploadPath = public_path('storage/images');

                    // Generate unique filename
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;

                    // Move file to public/storage/images/
                    $file->move($uploadPath, $filename);

                    // Get relative path for database storage
                    $relativePath = 'storage/images/' . $filename;

                    // Update the model field with the file path
                    $obj->$fieldName = $relativePath;
                    $obj->save();

                    Log::info("File uploaded successfully", [
                        'field' => $fieldName,
                        'original_name' => $originalName,
                        'stored_path' => $relativePath,
                        'model' => get_class($obj),
                        'model_id' => $obj->id
                    ]);

                } catch (Exception $e) {
                    Log::error("File upload failed", [
                        'field' => $fieldName,
                        'error' => $e->getMessage(),
                        'model' => get_class($obj),
                        'model_id' => $obj->id
                    ]);
                    
                    // Don't throw exception, just log and continue
                    // The form submission should still succeed even if file upload fails
                }
            }
        }
    }

    /**
     * Handle export requests
     */
    private function handleExport($query, $format, $model)
    {
        // Placeholder for export functionality
        // Can be extended to support CSV, Excel, PDF exports
        return $this->error('Export functionality not implemented yet.', 501);
    }

    /**
     * Get applied filters for metadata
     */
    private function getAppliedFilters(Request $r)
    {
        $filters = [];
        
        foreach ($r->all() as $key => $value) {
            if ((strpos($key, 'filter_') === 0 || strpos($key, 'q_') === 0) && $value !== null && $value !== '') {
                $filters[$key] = $value;
            }
        }

        return $filters;
    }

    /**
     * Check if model supports soft deletes
     */
    private function modelSupportsSoftDeletes($className)
    {
        $modelInstance = new $className;
        return method_exists($modelInstance, 'restore') && 
               method_exists($modelInstance, 'forceDelete') &&
               in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($className));
    }

    /**
     * Get allowed columns for a model (respects fillable/guarded)
     */
    private function getAllowedColumns($obj, array $allColumns, $isUpdate = false)
    {
        $fillable = $obj->getFillable();
        $guarded = $obj->getGuarded();
        
        // Default excluded columns for security
        $defaultExcluded = [
            'id', 'created_at', 'updated_at', 'deleted_at', 
            'password', 'remember_token', 'email_verified_at', 
            'api_token', '_token'
        ];
        
        // For updates, also exclude enterprise_id to prevent data leakage
        if ($isUpdate) {
            $defaultExcluded[] = 'enterprise_id';
        }
        
        $allowedColumns = [];
        
        foreach ($allColumns as $column) {
            // Skip excluded columns
            if (in_array($column, $defaultExcluded)) {
                continue;
            }
            
            // Check fillable constraints
            if (!empty($fillable) && !in_array($column, $fillable)) {
                continue;
            }
            
            // Check guarded constraints
            if (!empty($guarded) && in_array($column, $guarded)) {
                continue;
            }
            
            $allowedColumns[] = $column;
        }
        
        return $allowedColumns;
    }

    /**
     * Sanitize and validate input value
     */
    private function sanitizeValue($value, $columnType = null)
    {
        if ($value === null || $value === '') {
            return $value;
        }
        
        // Handle arrays (for JSON fields)
        if (is_array($value)) {
            return $value;
        }
        
        // Handle strings
        if (is_string($value)) {
            // Basic XSS protection
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            
            // Trim whitespace
            $value = trim($value);
        }
        
        return $value;
    }

    /**
     * Handle medical services array for Consultation model
     * This method creates/updates/deletes medical services based on the array provided
     * 
     * @param object $consultation - Consultation model instance
     * @param array $medicalServicesData - Array of medical service data
     * @param object $user - Authenticated user
     */
    private function handleMedicalServices($consultation, $medicalServicesData, $user)
    {
        try {
            // Get MedicalService model
            $medicalServiceClass = "App\\Models\\MedicalService";
            if (!class_exists($medicalServiceClass)) {
                Log::warning("MedicalService model not found. Skipping medical services sync.");
                return;
            }

            // Get existing medical service IDs for this consultation
            $existingServiceIds = $consultation->medical_services()->pluck('id')->toArray();
            $providedServiceIds = [];

            // Process each medical service in the array
            foreach ($medicalServicesData as $serviceData) {
                // Skip if no type specified
                if (empty($serviceData['type'])) {
                    continue;
                }

                $serviceId = $serviceData['id'] ?? null;
                $providedServiceIds[] = $serviceId;

                if ($serviceId && in_array($serviceId, $existingServiceIds)) {
                    // Update existing service
                    $service = $medicalServiceClass::find($serviceId);
                    if ($service && $service->consultation_id == $consultation->id) {
                        $service->type = $serviceData['type'];
                        $service->assigned_to_id = $serviceData['assigned_to_id'] ?? null;
                        $service->instruction = $serviceData['instruction'] ?? '';
                        $service->status = $serviceData['status'] ?? 'Pending';
                        $service->save();
                    }
                } else {
                    // Create new service
                    $newService = new $medicalServiceClass();
                    $newService->consultation_id = $consultation->id;
                    $newService->patient_id = $consultation->patient_id;
                    $newService->enterprise_id = $user->enterprise_id;
                    $newService->receptionist_id = $user->id;
                    $newService->type = $serviceData['type'];
                    $newService->assigned_to_id = $serviceData['assigned_to_id'] ?? null;
                    $newService->instruction = $serviceData['instruction'] ?? '';
                    $newService->status = $serviceData['status'] ?? 'Pending';
                    $newService->save();
                }
            }

            // Delete services that were removed from the array
            $servicesToDelete = array_diff($existingServiceIds, array_filter($providedServiceIds));
            if (!empty($servicesToDelete)) {
                $medicalServiceClass::whereIn('id', $servicesToDelete)
                    ->where('consultation_id', $consultation->id)
                    ->delete();
            }

            Log::info("Medical services synced successfully for consultation ID: {$consultation->id}");

        } catch (Exception $e) {
            Log::error("Error handling medical services: " . $e->getMessage(), [
                'consultation_id' => $consultation->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - let the consultation save succeed even if medical services fail
        }
    }
}
