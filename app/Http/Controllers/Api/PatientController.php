<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Consultation;
use App\Models\PatientRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Patient API Controller
 * 
 * Handles patient-related API endpoints including:
 * - Patient management
 * - Medical records
 * - Consultation history
 * - Appointments
 */
class PatientController extends BaseApiController
{
    /**
     * Get all patients (with enterprise scope)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $params = $this->getPaginationParams($request);
            
            $query = User::where('user_type', 'patient');
            $query = $this->applyEnterpriseScope($query);

            // Apply filters
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->has('status')) {
                $query->where('is_active', $request->get('status') === 'active');
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $patients = $query->paginate($params['per_page'], ['*'], 'page', $params['page']);

            $this->logApiActivity('patients_list', [
                'total' => $patients->total(),
                'filters' => $request->only(['search', 'status']),
            ]);

            return $this->paginatedResponse($patients, 'Patients retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get a specific patient
     */
    public function show(Request $request, int $patientId): JsonResponse
    {
        try {
            $query = User::where('user_type', 'patient')->where('id', $patientId);
            $query = $this->applyEnterpriseScope($query);
            
            $patient = $query->first();

            if (!$patient) {
                return $this->errorResponse('Patient not found', 404);
            }

            // Load relationships if requested
            $includes = $request->get('include', []);
            if (is_string($includes)) {
                $includes = explode(',', $includes);
            }

            $allowedIncludes = ['consultations', 'medical_records', 'appointments'];
            $includes = array_intersect($includes, $allowedIncludes);

            if (in_array('consultations', $includes)) {
                $patient->load(['consultations' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(10);
                }]);
            }

            if (in_array('medical_records', $includes)) {
                $patient->load(['patientRecords' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(10);
                }]);
            }

            $this->logApiActivity('patient_view', [
                'patient_id' => $patient->id,
                'includes' => $includes,
            ]);

            return $this->successResponse([
                'patient' => $this->transformPatient($patient, $includes),
            ], 'Patient retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Create a new patient
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateRequest($request, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:8',
                'date_of_birth' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'blood_group' => 'nullable|string|max:10',
                'address' => 'nullable|string|max:500',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'medical_history' => 'nullable|string',
                'allergies' => 'nullable|string',
                'current_medications' => 'nullable|string',
            ]);

            $patient = DB::transaction(function () use ($validated) {
                // Create user record
                $patient = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => bcrypt($validated['password']),
                    'user_type' => 'patient',
                    'enterprise_id' => $this->getCurrentEnterpriseId(),
                    'is_active' => true,
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'blood_group' => $validated['blood_group'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                    'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                ]);

                // Create initial patient record if medical info provided
                if (!empty($validated['medical_history']) || !empty($validated['allergies']) || !empty($validated['current_medications'])) {
                    PatientRecord::create([
                        'patient_id' => $patient->id,
                        'enterprise_id' => $this->getCurrentEnterpriseId(),
                        'record_type' => 'initial_assessment',
                        'medical_history' => $validated['medical_history'] ?? null,
                        'allergies' => $validated['allergies'] ?? null,
                        'current_medications' => $validated['current_medications'] ?? null,
                        'created_by' => auth()->id(),
                    ]);
                }

                return $patient;
            });

            $this->logApiActivity('patient_created', [
                'patient_id' => $patient->id,
                'created_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'patient' => $this->transformPatient($patient),
            ], 'Patient created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update a patient
     */
    public function update(Request $request, int $patientId): JsonResponse
    {
        try {
            $query = User::where('user_type', 'patient')->where('id', $patientId);
            $query = $this->applyEnterpriseScope($query);
            
            $patient = $query->first();

            if (!$patient) {
                return $this->errorResponse('Patient not found', 404);
            }

            $validated = $this->validateRequest($request, [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $patient->id,
                'phone' => 'sometimes|string|max:20',
                'date_of_birth' => 'sometimes|date',
                'gender' => 'sometimes|in:male,female,other',
                'blood_group' => 'nullable|string|max:10',
                'address' => 'nullable|string|max:500',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'is_active' => 'sometimes|boolean',
            ]);

            $patient->update($validated);

            $this->logApiActivity('patient_updated', [
                'patient_id' => $patient->id,
                'updated_fields' => array_keys($validated),
                'updated_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'patient' => $this->transformPatient($patient->fresh()),
            ], 'Patient updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get patient's consultation history
     */
    public function consultations(Request $request, int $patientId): JsonResponse
    {
        try {
            $query = User::where('user_type', 'patient')->where('id', $patientId);
            $query = $this->applyEnterpriseScope($query);
            
            $patient = $query->first();

            if (!$patient) {
                return $this->errorResponse('Patient not found', 404);
            }

            $params = $this->getPaginationParams($request);
            
            $consultationsQuery = Consultation::where('patient_id', $patientId);
            $consultationsQuery = $this->applyEnterpriseScope($consultationsQuery);

            // Apply filters
            if ($request->has('status')) {
                $consultationsQuery->where('status', $request->get('status'));
            }

            if ($request->has('date_from')) {
                $consultationsQuery->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $consultationsQuery->whereDate('created_at', '<=', $request->get('date_to'));
            }

            $consultationsQuery->orderBy('created_at', 'desc');
            $consultations = $consultationsQuery->paginate($params['per_page'], ['*'], 'page', $params['page']);

            $this->logApiActivity('patient_consultations_view', [
                'patient_id' => $patientId,
                'total_consultations' => $consultations->total(),
            ]);

            return $this->paginatedResponse($consultations, 'Patient consultations retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get patient's medical records
     */
    public function medicalRecords(Request $request, int $patientId): JsonResponse
    {
        try {
            $query = User::where('user_type', 'patient')->where('id', $patientId);
            $query = $this->applyEnterpriseScope($query);
            
            $patient = $query->first();

            if (!$patient) {
                return $this->errorResponse('Patient not found', 404);
            }

            $params = $this->getPaginationParams($request);
            
            $recordsQuery = PatientRecord::where('patient_id', $patientId);
            $recordsQuery = $this->applyEnterpriseScope($recordsQuery);

            // Apply filters
            if ($request->has('record_type')) {
                $recordsQuery->where('record_type', $request->get('record_type'));
            }

            if ($request->has('date_from')) {
                $recordsQuery->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $recordsQuery->whereDate('created_at', '<=', $request->get('date_to'));
            }

            $recordsQuery->orderBy('created_at', 'desc');
            $records = $recordsQuery->paginate($params['per_page'], ['*'], 'page', $params['page']);

            $this->logApiActivity('patient_medical_records_view', [
                'patient_id' => $patientId,
                'total_records' => $records->total(),
            ]);

            return $this->paginatedResponse($records, 'Patient medical records retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Transform patient for API response
     */
    private function transformPatient(User $patient, array $includes = []): array
    {
        $data = [
            'id' => $patient->id,
            'name' => $patient->name,
            'email' => $patient->email,
            'phone' => $patient->phone,
            'date_of_birth' => $patient->date_of_birth,
            'age' => $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : null,
            'gender' => $patient->gender,
            'blood_group' => $patient->blood_group,
            'address' => $patient->address,
            'emergency_contact_name' => $patient->emergency_contact_name,
            'emergency_contact_phone' => $patient->emergency_contact_phone,
            'is_active' => $patient->is_active,
            'created_at' => $patient->created_at->toISOString(),
            'updated_at' => $patient->updated_at->toISOString(),
        ];

        // Add includes if specified
        if (in_array('consultations', $includes) && $patient->relationLoaded('consultations')) {
            $data['consultations'] = $patient->consultations->map(function ($consultation) {
                return [
                    'id' => $consultation->id,
                    'status' => $consultation->status,
                    'symptoms' => $consultation->symptoms,
                    'diagnosis' => $consultation->diagnosis,
                    'created_at' => $consultation->created_at->toISOString(),
                ];
            });
        }

        if (in_array('medical_records', $includes) && $patient->relationLoaded('patientRecords')) {
            $data['medical_records'] = $patient->patientRecords->map(function ($record) {
                return [
                    'id' => $record->id,
                    'record_type' => $record->record_type,
                    'medical_history' => $record->medical_history,
                    'allergies' => $record->allergies,
                    'current_medications' => $record->current_medications,
                    'created_at' => $record->created_at->toISOString(),
                ];
            });
        }

        return $data;
    }
}
