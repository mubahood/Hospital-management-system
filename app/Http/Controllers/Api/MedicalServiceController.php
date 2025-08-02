<?php

namespace App\Http\Controllers\Api;

use App\Models\MedicalService;
use App\Models\MedicalServiceItem;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Medical Services API Controller
 * 
 * Handles medical services-related API endpoints including:
 * - Service catalog management
 * - Service provisioning
 * - Pricing and billing
 * - Service history
 */
class MedicalServiceController extends BaseApiController
{
    /**
     * Get all medical service items (catalog)
     */
    public function serviceItems(Request $request): JsonResponse
    {
        try {
            $params = $this->getPaginationParams($request);
            
            $query = MedicalServiceItem::query();
            $query = $this->applyEnterpriseScope($query);

            // Apply filters
            if ($request->has('category')) {
                $query->where('category', $request->get('category'));
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            if ($request->has('price_min')) {
                $query->where('price', '>=', $request->get('price_min'));
            }

            if ($request->has('price_max')) {
                $query->where('price', '<=', $request->get('price_max'));
            }

            // Sort
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $serviceItems = $query->paginate($params['per_page'], ['*'], 'page', $params['page']);

            $this->logApiActivity('medical_service_items_list', [
                'total' => $serviceItems->total(),
                'filters' => $request->only(['category', 'is_active', 'search']),
            ]);

            return $this->paginatedResponse($serviceItems->through(function ($item) {
                return $this->transformServiceItem($item);
            }), 'Medical service items retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get a specific medical service item
     */
    public function serviceItem(Request $request, int $itemId): JsonResponse
    {
        try {
            $query = MedicalServiceItem::query();
            $query = $this->applyEnterpriseScope($query);
            
            $item = $query->find($itemId);

            if (!$item) {
                return $this->errorResponse('Medical service item not found', 404);
            }

            $this->logApiActivity('medical_service_item_view', [
                'item_id' => $item->id,
            ]);

            return $this->successResponse([
                'service_item' => $this->transformServiceItem($item, true),
            ], 'Medical service item retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Create a new medical service item
     */
    public function createServiceItem(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateRequest($request, [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:medical_service_items,code',
                'description' => 'nullable|string',
                'category' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'unit' => 'required|string|max:50',
                'is_active' => 'sometimes|boolean',
                'requires_approval' => 'sometimes|boolean',
                'preparation_instructions' => 'nullable|string',
                'post_service_instructions' => 'nullable|string',
            ]);

            $serviceItem = MedicalServiceItem::create(array_merge($validated, [
                'enterprise_id' => $this->getCurrentEnterpriseId(),
                'created_by' => auth()->id(),
                'is_active' => $validated['is_active'] ?? true,
                'requires_approval' => $validated['requires_approval'] ?? false,
            ]));

            $this->logApiActivity('medical_service_item_created', [
                'item_id' => $serviceItem->id,
                'created_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'service_item' => $this->transformServiceItem($serviceItem, true),
            ], 'Medical service item created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update a medical service item
     */
    public function updateServiceItem(Request $request, int $itemId): JsonResponse
    {
        try {
            $query = MedicalServiceItem::query();
            $query = $this->applyEnterpriseScope($query);
            
            $item = $query->find($itemId);

            if (!$item) {
                return $this->errorResponse('Medical service item not found', 404);
            }

            $validated = $this->validateRequest($request, [
                'name' => 'sometimes|string|max:255',
                'code' => 'sometimes|string|max:50|unique:medical_service_items,code,' . $item->id,
                'description' => 'nullable|string',
                'category' => 'sometimes|string|max:100',
                'price' => 'sometimes|numeric|min:0',
                'unit' => 'sometimes|string|max:50',
                'is_active' => 'sometimes|boolean',
                'requires_approval' => 'sometimes|boolean',
                'preparation_instructions' => 'nullable|string',
                'post_service_instructions' => 'nullable|string',
            ]);

            $item->update(array_merge($validated, [
                'updated_by' => auth()->id(),
            ]));

            $this->logApiActivity('medical_service_item_updated', [
                'item_id' => $item->id,
                'updated_fields' => array_keys($validated),
                'updated_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'service_item' => $this->transformServiceItem($item->fresh(), true),
            ], 'Medical service item updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get all medical services (provided services)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $params = $this->getPaginationParams($request);
            
            $query = MedicalService::with(['consultation.patient', 'serviceItem']);
            $query = $this->applyEnterpriseScope($query);

            // Apply filters
            if ($request->has('consultation_id')) {
                $query->where('consultation_id', $request->get('consultation_id'));
            }

            if ($request->has('patient_id')) {
                $query->whereHas('consultation', function ($q) use ($request) {
                    $q->where('patient_id', $request->get('patient_id'));
                });
            }

            if ($request->has('service_item_id')) {
                $query->where('service_id', $request->get('service_item_id'));
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $services = $query->paginate($params['per_page'], ['*'], 'page', $params['page']);

            $this->logApiActivity('medical_services_list', [
                'total' => $services->total(),
                'filters' => $request->only(['consultation_id', 'patient_id', 'service_item_id']),
            ]);

            return $this->paginatedResponse($services->through(function ($service) {
                return $this->transformMedicalService($service);
            }), 'Medical services retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get a specific medical service
     */
    public function show(Request $request, int $serviceId): JsonResponse
    {
        try {
            $query = MedicalService::with(['consultation.patient', 'serviceItem']);
            $query = $this->applyEnterpriseScope($query);
            
            $service = $query->find($serviceId);

            if (!$service) {
                return $this->errorResponse('Medical service not found', 404);
            }

            $this->logApiActivity('medical_service_view', [
                'service_id' => $service->id,
                'consultation_id' => $service->consultation_id,
            ]);

            return $this->successResponse([
                'medical_service' => $this->transformMedicalService($service, true),
            ], 'Medical service retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Create a new medical service
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateRequest($request, [
                'consultation_id' => 'required|exists:consultations,id',
                'service_id' => 'required|exists:medical_service_items,id',
                'quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string',
                'performed_at' => 'nullable|date',
                'performed_by' => 'nullable|exists:users,id',
            ]);

            // Verify consultation belongs to enterprise
            $consultation = Consultation::where('id', $validated['consultation_id'])
                                      ->where('enterprise_id', $this->getCurrentEnterpriseId())
                                      ->first();

            if (!$consultation) {
                return $this->errorResponse('Consultation not found', 404);
            }

            // Verify service item belongs to enterprise
            $serviceItem = MedicalServiceItem::where('id', $validated['service_id'])
                                           ->where('enterprise_id', $this->getCurrentEnterpriseId())
                                           ->first();

            if (!$serviceItem) {
                return $this->errorResponse('Service item not found', 404);
            }

            if (!$serviceItem->is_active) {
                return $this->errorResponse('Service item is not active', 400);
            }

            $service = MedicalService::create(array_merge($validated, [
                'enterprise_id' => $this->getCurrentEnterpriseId(),
                'created_by' => auth()->id(),
                'performed_at' => $validated['performed_at'] ?? now(),
                'performed_by' => $validated['performed_by'] ?? auth()->id(),
            ]));

            $service->load(['consultation.patient', 'serviceItem']);

            $this->logApiActivity('medical_service_created', [
                'service_id' => $service->id,
                'consultation_id' => $service->consultation_id,
                'service_item_id' => $service->service_id,
                'created_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'medical_service' => $this->transformMedicalService($service, true),
            ], 'Medical service created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update a medical service
     */
    public function update(Request $request, int $serviceId): JsonResponse
    {
        try {
            $query = MedicalService::query();
            $query = $this->applyEnterpriseScope($query);
            
            $service = $query->find($serviceId);

            if (!$service) {
                return $this->errorResponse('Medical service not found', 404);
            }

            $validated = $this->validateRequest($request, [
                'quantity' => 'sometimes|integer|min:1',
                'notes' => 'nullable|string',
                'performed_at' => 'sometimes|date',
                'performed_by' => 'sometimes|exists:users,id',
            ]);

            $service->update(array_merge($validated, [
                'updated_by' => auth()->id(),
            ]));

            $service->load(['consultation.patient', 'serviceItem']);

            $this->logApiActivity('medical_service_updated', [
                'service_id' => $service->id,
                'updated_fields' => array_keys($validated),
                'updated_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'medical_service' => $this->transformMedicalService($service, true),
            ], 'Medical service updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get service categories
     */
    public function categories(Request $request): JsonResponse
    {
        try {
            $query = MedicalServiceItem::query();
            $query = $this->applyEnterpriseScope($query);
            
            $categories = $query->select('category')
                               ->distinct()
                               ->whereNotNull('category')
                               ->where('category', '!=', '')
                               ->pluck('category');

            $this->logApiActivity('medical_service_categories_list', [
                'total_categories' => $categories->count(),
            ]);

            return $this->successResponse([
                'categories' => $categories->values(),
            ], 'Service categories retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get service statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = MedicalService::query();
            $query = $this->applyEnterpriseScope($query);

            // Apply date filter if provided
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            $totalServices = $query->count();
            $totalQuantity = $query->sum('quantity');

            // Get top services
            $topServices = MedicalService::select('service_id', DB::raw('COUNT(*) as usage_count'), DB::raw('SUM(quantity) as total_quantity'))
                ->with('serviceItem')
                ->whereHas('serviceItem')
                ->when($request->has('date_from'), function ($q) use ($request) {
                    $q->whereDate('created_at', '>=', $request->get('date_from'));
                })
                ->when($request->has('date_to'), function ($q) use ($request) {
                    $q->whereDate('created_at', '<=', $request->get('date_to'));
                })
                ->groupBy('service_id')
                ->orderBy('usage_count', 'desc')
                ->limit(10)
                ->get();

            $statistics = [
                'total_services_provided' => $totalServices,
                'total_service_quantity' => $totalQuantity,
                'top_services' => $topServices->map(function ($service) {
                    return [
                        'service_item' => [
                            'id' => $service->serviceItem->id,
                            'name' => $service->serviceItem->name,
                            'category' => $service->serviceItem->category,
                        ],
                        'usage_count' => $service->usage_count,
                        'total_quantity' => $service->total_quantity,
                    ];
                }),
                'period' => [
                    'from' => $request->get('date_from'),
                    'to' => $request->get('date_to'),
                ],
            ];

            $this->logApiActivity('medical_service_statistics', [
                'total_services' => $totalServices,
                'period' => $statistics['period'],
            ]);

            return $this->successResponse($statistics, 'Service statistics retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Transform service item for API response
     */
    private function transformServiceItem(MedicalServiceItem $item, bool $detailed = false): array
    {
        $data = [
            'id' => $item->id,
            'name' => $item->name,
            'code' => $item->code,
            'category' => $item->category,
            'price' => $item->price,
            'unit' => $item->unit,
            'is_active' => $item->is_active,
            'requires_approval' => $item->requires_approval,
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'description' => $item->description,
                'preparation_instructions' => $item->preparation_instructions,
                'post_service_instructions' => $item->post_service_instructions,
                'created_at' => $item->created_at->toISOString(),
                'updated_at' => $item->updated_at->toISOString(),
            ]);
        }

        return $data;
    }

    /**
     * Transform medical service for API response
     */
    private function transformMedicalService(MedicalService $service, bool $detailed = false): array
    {
        $data = [
            'id' => $service->id,
            'consultation_id' => $service->consultation_id,
            'patient' => [
                'id' => $service->consultation->patient->id,
                'name' => $service->consultation->patient->name,
            ],
            'service_item' => [
                'id' => $service->serviceItem->id,
                'name' => $service->serviceItem->name,
                'category' => $service->serviceItem->category,
                'price' => $service->serviceItem->price,
                'unit' => $service->serviceItem->unit,
            ],
            'quantity' => $service->quantity,
            'total_cost' => $service->serviceItem->price * $service->quantity,
            'performed_at' => $service->performed_at->toISOString(),
            'created_at' => $service->created_at->toISOString(),
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'notes' => $service->notes,
                'performed_by' => $service->performedBy ? [
                    'id' => $service->performedBy->id,
                    'name' => $service->performedBy->name,
                ] : null,
                'updated_at' => $service->updated_at->toISOString(),
            ]);
        }

        return $data;
    }
}
