<?php

namespace App\Http\Controllers\Api;

use App\Models\Consultation;
use App\Models\User;
use App\Models\MedicalService;
use App\Models\BillingItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Consultation API Controller
 * 
 * Handles consultation-related API endpoints including:
 * - Consultation management
 * - Medical services
 * - Billing integration
 * - Status updates
 */
class ConsultationController extends BaseApiController
{
    /**
     * Get all consultations (with enterprise scope)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $params = $this->getPaginationParams($request);
            
            $query = Consultation::with(['patient', 'doctor']);
            $query = $this->applyEnterpriseScope($query);

            // Apply filters
            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->get('patient_id'));
            }

            if ($request->has('doctor_id')) {
                $query->where('doctor_id', $request->get('doctor_id'));
            }

            if ($request->has('status')) {
                $status = $request->get('status');
                if (is_array($status)) {
                    $query->whereIn('status', $status);
                } else {
                    $query->where('status', $status);
                }
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('symptoms', 'like', "%{$search}%")
                      ->orWhere('diagnosis', 'like', "%{$search}%")
                      ->orWhereHas('patient', function ($pq) use ($search) {
                          $pq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $consultations = $query->paginate($params['per_page'], ['*'], 'page', $params['page']);

            $this->logApiActivity('consultations_list', [
                'total' => $consultations->total(),
                'filters' => $request->only(['patient_id', 'doctor_id', 'status', 'date_from', 'date_to']),
            ]);

            return $this->paginatedResponse($consultations->through(function ($consultation) {
                return $this->transformConsultation($consultation);
            }), 'Consultations retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get a specific consultation
     */
    public function show(Request $request, int $consultationId): JsonResponse
    {
        try {
            $query = Consultation::with(['patient', 'doctor', 'medicalServices', 'billingItems']);
            $query = $this->applyEnterpriseScope($query);
            
            $consultation = $query->find($consultationId);

            if (!$consultation) {
                return $this->errorResponse('Consultation not found', 404);
            }

            $this->logApiActivity('consultation_view', [
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
            ]);

            return $this->successResponse([
                'consultation' => $this->transformConsultation($consultation, true),
            ], 'Consultation retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Create a new consultation
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateRequest($request, [
                'patient_id' => 'required|exists:users,id',
                'doctor_id' => 'required|exists:users,id',
                'symptoms' => 'required|string',
                'vital_signs' => 'nullable|json',
                'examination_notes' => 'nullable|string',
                'diagnosis' => 'nullable|string',
                'treatment_plan' => 'nullable|string',
                'follow_up_date' => 'nullable|date|after:today',
                'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
                'medical_services' => 'nullable|array',
                'medical_services.*.service_id' => 'required_with:medical_services|exists:medical_services,id',
                'medical_services.*.quantity' => 'required_with:medical_services|integer|min:1',
                'medical_services.*.notes' => 'nullable|string',
            ]);

            // Verify patient and doctor exist and belong to enterprise
            $patient = User::where('id', $validated['patient_id'])
                          ->where('user_type', 'patient')
                          ->first();
            $doctor = User::where('id', $validated['doctor_id'])
                         ->where('user_type', 'doctor')
                         ->first();

            if (!$patient || !$doctor) {
                return $this->errorResponse('Invalid patient or doctor', 400);
            }

            $consultation = DB::transaction(function () use ($validated) {
                // Create consultation
                $consultation = Consultation::create([
                    'patient_id' => $validated['patient_id'],
                    'doctor_id' => $validated['doctor_id'],
                    'enterprise_id' => $this->getCurrentEnterpriseId(),
                    'symptoms' => $validated['symptoms'],
                    'vital_signs' => $validated['vital_signs'] ?? null,
                    'examination_notes' => $validated['examination_notes'] ?? null,
                    'diagnosis' => $validated['diagnosis'] ?? null,
                    'treatment_plan' => $validated['treatment_plan'] ?? null,
                    'follow_up_date' => $validated['follow_up_date'] ?? null,
                    'status' => $validated['status'] ?? 'scheduled',
                    'created_by' => auth()->id(),
                ]);

                // Add medical services if provided
                if (!empty($validated['medical_services'])) {
                    foreach ($validated['medical_services'] as $serviceData) {
                        MedicalService::create([
                            'consultation_id' => $consultation->id,
                            'service_id' => $serviceData['service_id'],
                            'quantity' => $serviceData['quantity'],
                            'notes' => $serviceData['notes'] ?? null,
                            'enterprise_id' => $this->getCurrentEnterpriseId(),
                            'created_by' => auth()->id(),
                        ]);
                    }
                }

                return $consultation;
            });

            $consultation->load(['patient', 'doctor', 'medicalServices', 'billingItems']);

            $this->logApiActivity('consultation_created', [
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'doctor_id' => $consultation->doctor_id,
                'created_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'consultation' => $this->transformConsultation($consultation, true),
            ], 'Consultation created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update a consultation
     */
    public function update(Request $request, int $consultationId): JsonResponse
    {
        try {
            $query = Consultation::query();
            $query = $this->applyEnterpriseScope($query);
            
            $consultation = $query->find($consultationId);

            if (!$consultation) {
                return $this->errorResponse('Consultation not found', 404);
            }

            $validated = $this->validateRequest($request, [
                'symptoms' => 'sometimes|string',
                'vital_signs' => 'nullable|json',
                'examination_notes' => 'nullable|string',
                'diagnosis' => 'nullable|string',
                'treatment_plan' => 'nullable|string',
                'follow_up_date' => 'nullable|date|after:today',
                'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
                'prescriptions' => 'nullable|string',
                'recommendations' => 'nullable|string',
            ]);

            $consultation->update(array_merge($validated, [
                'updated_by' => auth()->id(),
            ]));

            $consultation->load(['patient', 'doctor', 'medicalServices', 'billingItems']);

            $this->logApiActivity('consultation_updated', [
                'consultation_id' => $consultation->id,
                'updated_fields' => array_keys($validated),
                'updated_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'consultation' => $this->transformConsultation($consultation, true),
            ], 'Consultation updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Add medical services to consultation
     */
    public function addMedicalServices(Request $request, int $consultationId): JsonResponse
    {
        try {
            $query = Consultation::query();
            $query = $this->applyEnterpriseScope($query);
            
            $consultation = $query->find($consultationId);

            if (!$consultation) {
                return $this->errorResponse('Consultation not found', 404);
            }

            $validated = $this->validateRequest($request, [
                'medical_services' => 'required|array|min:1',
                'medical_services.*.service_id' => 'required|exists:medical_service_items,id',
                'medical_services.*.quantity' => 'required|integer|min:1',
                'medical_services.*.notes' => 'nullable|string',
            ]);

            $addedServices = [];

            DB::transaction(function () use ($consultation, $validated, &$addedServices) {
                foreach ($validated['medical_services'] as $serviceData) {
                    $medicalService = MedicalService::create([
                        'consultation_id' => $consultation->id,
                        'service_id' => $serviceData['service_id'],
                        'quantity' => $serviceData['quantity'],
                        'notes' => $serviceData['notes'] ?? null,
                        'enterprise_id' => $this->getCurrentEnterpriseId(),
                        'created_by' => auth()->id(),
                    ]);

                    $addedServices[] = $medicalService;
                }
            });

            $this->logApiActivity('consultation_medical_services_added', [
                'consultation_id' => $consultation->id,
                'services_count' => count($addedServices),
                'added_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'added_services' => $addedServices,
                'total_services' => $consultation->medicalServices()->count() + count($addedServices),
            ], 'Medical services added successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get consultation billing summary
     */
    public function billingSummary(Request $request, int $consultationId): JsonResponse
    {
        try {
            $query = Consultation::with(['billingItems', 'medicalServices']);
            $query = $this->applyEnterpriseScope($query);
            
            $consultation = $query->find($consultationId);

            if (!$consultation) {
                return $this->errorResponse('Consultation not found', 404);
            }

            $billingItems = $consultation->billingItems;
            $medicalServices = $consultation->medicalServices;

            $summary = [
                'consultation_id' => $consultation->id,
                'patient' => [
                    'id' => $consultation->patient->id,
                    'name' => $consultation->patient->name,
                ],
                'billing_items' => $billingItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'description' => $item->description,
                        'amount' => $item->amount,
                        'quantity' => $item->quantity,
                        'total' => $item->amount * $item->quantity,
                    ];
                }),
                'medical_services' => $medicalServices->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'service_name' => $service->serviceItem->name ?? 'N/A',
                        'quantity' => $service->quantity,
                        'unit_price' => $service->serviceItem->price ?? 0,
                        'total' => ($service->serviceItem->price ?? 0) * $service->quantity,
                    ];
                }),
                'totals' => [
                    'billing_items_total' => $billingItems->sum(function ($item) {
                        return $item->amount * $item->quantity;
                    }),
                    'medical_services_total' => $medicalServices->sum(function ($service) {
                        return ($service->serviceItem->price ?? 0) * $service->quantity;
                    }),
                    'grand_total' => $billingItems->sum(function ($item) {
                        return $item->amount * $item->quantity;
                    }) + $medicalServices->sum(function ($service) {
                        return ($service->serviceItem->price ?? 0) * $service->quantity;
                    }),
                ],
                'status' => $consultation->billing_status ?? 'pending',
            ];

            $this->logApiActivity('consultation_billing_summary', [
                'consultation_id' => $consultation->id,
                'grand_total' => $summary['totals']['grand_total'],
            ]);

            return $this->successResponse($summary, 'Billing summary retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Transform consultation for API response
     */
    private function transformConsultation(Consultation $consultation, bool $detailed = false): array
    {
        $data = [
            'id' => $consultation->id,
            'patient' => [
                'id' => $consultation->patient->id,
                'name' => $consultation->patient->name,
                'email' => $consultation->patient->email,
            ],
            'doctor' => [
                'id' => $consultation->doctor->id,
                'name' => $consultation->doctor->name,
                'email' => $consultation->doctor->email,
            ],
            'symptoms' => $consultation->symptoms,
            'status' => $consultation->status,
            'created_at' => $consultation->created_at->toISOString(),
            'updated_at' => $consultation->updated_at->toISOString(),
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'vital_signs' => $consultation->vital_signs ? json_decode($consultation->vital_signs, true) : null,
                'examination_notes' => $consultation->examination_notes,
                'diagnosis' => $consultation->diagnosis,
                'treatment_plan' => $consultation->treatment_plan,
                'prescriptions' => $consultation->prescriptions,
                'recommendations' => $consultation->recommendations,
                'follow_up_date' => $consultation->follow_up_date,
                'medical_services_count' => $consultation->medicalServices ? $consultation->medicalServices->count() : 0,
                'billing_items_count' => $consultation->billingItems ? $consultation->billingItems->count() : 0,
            ]);

            if ($consultation->relationLoaded('medicalServices')) {
                $data['medical_services'] = $consultation->medicalServices->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'service_name' => $service->serviceItem->name ?? 'N/A',
                        'quantity' => $service->quantity,
                        'notes' => $service->notes,
                    ];
                });
            }

            if ($consultation->relationLoaded('billingItems')) {
                $data['billing_items'] = $consultation->billingItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'description' => $item->description,
                        'amount' => $item->amount,
                        'quantity' => $item->quantity,
                        'total' => $item->amount * $item->quantity,
                    ];
                });
            }
        }

        return $data;
    }
}
