<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalService;
use App\Models\Consultation;
use App\Models\User;
use App\Models\StockItem;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MedicalServiceController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of medical services
     */
    public function index(Request $request)
    {
        try {
            // Get consultation IDs of ongoing consultations
            $consultationIds = Consultation::whereIn('main_status', ['Ongoing', 'Billing'])
                ->pluck('id')
                ->toArray();

            $query = MedicalService::with(['consultation.patient', 'assigned_to'])
                ->whereIn('consultation_id', $consultationIds)
                ->orderBy('created_at', 'desc');

            // Apply filters if provided
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            if ($request->has('consultation_id') && !empty($request->consultation_id)) {
                $query->where('consultation_id', $request->consultation_id);
            }

            if ($request->has('assigned_to_id') && !empty($request->assigned_to_id)) {
                $query->where('assigned_to_id', $request->assigned_to_id);
            }

            // Pagination
            $perPage = min(100, max(1, $request->get('per_page', 20)));
            $services = $query->paginate($perPage);

            // Transform the data
            $services->getCollection()->transform(function ($service) {
                return [
                    'id' => $service->id,
                    'consultation_id' => $service->consultation_id,
                    'consultation_number' => $service->consultation?->consultation_number ?? 'N/A',
                    'patient_id' => $service->consultation?->patient_id ?? null,
                    'patient_name' => $service->consultation?->patient?->name ?? 'N/A',
                    'assigned_to_id' => $service->assigned_to_id,
                    'specialist_name' => $service->assigned_to?->name ?? 'N/A',
                    'type' => $service->type,
                    'status' => $service->status,
                    'instruction' => $service->instruction,
                    'specialist_outcome' => $service->specialist_outcome,
                    'description' => $service->description,
                    'remarks' => $service->remarks,
                    'created_at' => $service->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $service->updated_at?->format('Y-m-d H:i:s'),
                ];
            });

            return $this->success([
                'items' => $services->items(),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ],
            ], 'Medical services retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Error retrieving medical services: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created medical service
     */
    public function store(Request $request)
    {
        try {
            // Validation rules - SIMPLIFIED to 4 fields only
            $validator = Validator::make($request->all(), [
                'consultation_id' => 'required|exists:consultations,id',
                'assigned_to_id' => [
                    'nullable',
                    'integer',
                    function ($attribute, $value, $fail) {
                        if ($value !== null && $value !== '') {
                            // Check if user exists without global scopes
                            $userExists = \DB::table('admin_users')->where('id', $value)->exists();
                            if (!$userExists) {
                                $fail('The selected assigned to id is invalid.');
                            }
                        }
                    },
                ],
                'type' => 'required|string|max:255', // Service type (maps to both type and description)
                'instruction' => 'nullable|string', // Special instructions
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get consultation details to populate patient_id and other fields
            $consultation = Consultation::find($request->consultation_id);
            if (!$consultation) {
                return $this->error('Consultation not found');
            }

            // Create the medical service - SIMPLIFIED to 4 fields
            $serviceData = [
                'consultation_id' => $request->consultation_id,
                'patient_id' => $consultation->patient_id,
                'assigned_to_id' => $request->assigned_to_id ?? null, // Optional
                'type' => $request->type, // Service type from form
                'status' => $request->status ?? 'Pending', // Default to Pending
                'instruction' => $request->instruction,
                'description' => $request->type, // Map type to description for compatibility
                'receptionist_id' => auth()->id(), // Current user creating the service
                'specialist_outcome' => '', // Empty initially
                'remarks' => '', // Empty initially
            ];

            $service = MedicalService::create($serviceData);

            // Load relationships for response
            $service->load(['consultation.patient', 'assigned_to']);

            return $this->success([
                'id' => $service->id,
                'consultation_id' => $service->consultation_id,
                'consultation_number' => $service->consultation?->consultation_number,
                'patient_id' => $service->patient_id,
                'patient_name' => $service->consultation?->patient?->name,
                'assigned_to_id' => $service->assigned_to_id,
                'specialist_name' => $service->assigned_to?->name,
                'type' => $service->type,
                'status' => $service->status,
                'instruction' => $service->instruction,
                'specialist_outcome' => $service->specialist_outcome,
                'created_at' => $service->created_at?->format('Y-m-d H:i:s'),
            ], 'Medical service created successfully');

        } catch (\Exception $e) {
            return $this->error('Error creating medical service: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified medical service
     */
    public function show($id)
    {
        try {
            $service = MedicalService::with(['consultation.patient', 'assigned_to', 'receptionist'])->find($id);

            if (!$service) {
                return $this->error('Medical service not found');
            }

            // Get current authenticated user
            $currentUserId = auth()->user()->id ?? null;
            $creatorId = $service->receptionist_id ?? null;
            
            // Determine if current user is the creator
            $isCreator = $currentUserId && $creatorId && ($currentUserId == $creatorId);

            return $this->success([
                    'id' => $service->id,
                    'consultation_id' => $service->consultation_id,
                    'consultation_number' => $service->consultation?->consultation_number,
                    'patient_id' => $service->patient_id,
                    'patient_name' => $service->consultation?->patient?->name,
                    'assigned_to_id' => $service->assigned_to_id,
                    'specialist_name' => $service->assigned_to?->name,
                    'type' => $service->type,
                    'status' => $service->status,
                    'instruction' => $service->instruction,
                    'specialist_outcome' => $service->specialist_outcome,
                    'primary_stock_item_id' => $service->primary_stock_item_id ?? null,
                    'stock_quantity_used' => $service->stock_quantity_used ?? null,
                    'stock_item_notes' => $service->stock_item_notes ?? null,
                    'receptionist_id' => $service->receptionist_id,
                    'receptionist_name' => $service->receptionist?->name ?? null,
                    'is_creator' => $isCreator,
                    'can_edit_original_fields' => $isCreator,
                'created_at' => $service->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $service->updated_at?->format('Y-m-d H:i:s'),
            ], 'Medical service retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Error retrieving medical service: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified medical service
     */
    public function update(Request $request, $id)
    {
        try {
            $service = MedicalService::find($id);

            if (!$service) {
                return $this->error('Medical service not found');
            }

            // Validation rules - SIMPLIFIED to 4 fields
            $validator = Validator::make($request->all(), [
                'consultation_id' => 'sometimes|required|exists:consultations,id',
                'assigned_to_id' => [
                    'nullable',
                    'integer',
                    function ($attribute, $value, $fail) {
                        if ($value !== null && $value !== '') {
                            // Check if user exists without global scopes
                            $userExists = \DB::table('admin_users')->where('id', $value)->exists();
                            if (!$userExists) {
                                $fail('The selected assigned to id is invalid.');
                            }
                        }
                    },
                ],
                'type' => 'sometimes|required|string|max:255',
                'instruction' => 'nullable|string',
                'status' => 'nullable|in:Pending,Ongoing,Completed',
                'specialist_outcome' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed: ' . $validator->errors()->first());
            }

            // Update the service - only accept simplified fields

            // Update the service - only accept simplified fields
            $updateData = $request->only([
                'consultation_id', 'assigned_to_id', 'type', 'instruction', 
                'status', 'specialist_outcome'
            ]);

            // Map type to description field for database compatibility
            if ($request->has('type')) {
                $updateData['description'] = $request->type;
            }

            $service->update(array_filter($updateData, function($value) {
                return $value !== null;
            }));

            // Load relationships for response
            $service->load(['consultation.patient', 'assigned_to']);

            return $this->success([
                'id' => $service->id,
                'consultation_id' => $service->consultation_id,
                'consultation_number' => $service->consultation?->consultation_number,
                'patient_id' => $service->patient_id,
                'patient_name' => $service->consultation?->patient?->name,
                'assigned_to_id' => $service->assigned_to_id,
                'specialist_name' => $service->assigned_to?->name,
                'type' => $service->type,
                'status' => $service->status,
                'instruction' => $service->instruction,
                'specialist_outcome' => $service->specialist_outcome,
                'description' => $service->description,
                'remarks' => $service->remarks,
                'updated_at' => $service->updated_at?->format('Y-m-d H:i:s'),
            ], 'Medical service updated successfully');

        } catch (\Exception $e) {
            return $this->error('Error updating medical service: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified medical service
     */
    public function destroy($id)
    {
        try {
            $service = MedicalService::find($id);

            if (!$service) {
                return $this->error('Medical service not found');
            }

            $service->delete();

            return $this->success([], 'Medical service deleted successfully');

        } catch (\Exception $e) {
            return $this->error('Error deleting medical service: ' . $e->getMessage());
        }
    }

    /**
     * Get consultations for dropdown with enhanced search and formatting
     */
    public function getConsultationsForDropdown(Request $request)
    {
        try {
            $searchQuery = $request->get('q', '');
            $consultationId = $request->get('consultation_id', null);
            $limit = min(50, max(1, $request->get('limit', 20)));

            // Build query for active consultations only
            $query = Consultation::with(['patient'])
                ->whereIn('main_status', ['Ongoing', 'Billing'])
                ->distinct() // Ensure unique results
                ->orderBy('consultations.created_at', 'desc'); // Specify table name to avoid ambiguity

            // If specific consultation ID is provided, fetch only that consultation
            if (!empty($consultationId)) {
                $query->where('consultations.id', $consultationId);
            }
            // Apply search if provided
            else if (!empty($searchQuery)) {
                $query->where(function($q) use ($searchQuery) {
                    // Search by consultation number
                    $q->where('consultations.consultation_number', 'LIKE', "%{$searchQuery}%")
                      // OR search by patient information
                      ->orWhereHas('patient', function($patientQuery) use ($searchQuery) {
                          $patientQuery->where(function($innerQ) use ($searchQuery) {
                              $innerQ->where('first_name', 'LIKE', "%{$searchQuery}%")
                                    ->orWhere('last_name', 'LIKE', "%{$searchQuery}%")
                                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchQuery}%"]);
                          });
                      });
                });
            }

            $consultations = $query->limit($limit)->get();

            // Format for dropdown
            $data = $consultations->map(function($consultation) {
                $patientName = 'Unknown Patient';
                if ($consultation->patient) {
                    $patientName = trim("{$consultation->patient->first_name} {$consultation->patient->last_name}");
                }

                $consultationNumber = $consultation->consultation_number ?: "CON-{$consultation->id}";
                $status = $consultation->main_status;
                $date = $consultation->created_at ? $consultation->created_at->format('M j, Y') : '';

                return [
                    'id' => $consultation->id,
                    'text' => "{$consultationNumber} - {$patientName}",
                    'consultation_number' => $consultationNumber,
                    'patient_name' => $patientName,
                    'patient_id' => $consultation->patient_id,
                    'status' => $status,
                    'date' => $date,
                    'details' => "{$consultationNumber} | {$patientName} | {$status} | {$date}"
                ];
            });

            return $this->success($data->values()->all(), 'Consultations retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Error retrieving consultations: ' . $e->getMessage());
        }
    }

    /**
     * Get employees/doctors for dropdown with enhanced search and formatting
     */
    public function getEmployeesForDropdown(Request $request)
    {
        try {
            $searchQuery = $request->get('q', '');
            $employeeId = $request->get('employee_id', null);
            $limit = min(50, max(1, $request->get('limit', 20)));
            $userType = $request->get('user_type', 'employee'); // Default to employees (lowercase)

            // Build query for employees/doctors - CASE INSENSITIVE
            // Status: 1 = Active, 0 = Inactive
            $query = User::whereRaw('LOWER(user_type) = ?', [strtolower($userType)])
                ->where('status', 1) // Active users only (numeric status)
                ->distinct() // Ensure unique results
                ->orderBy('first_name', 'asc')
                ->orderBy('last_name', 'asc');

            // If specific employee ID is provided, fetch only that employee
            if (!empty($employeeId)) {
                $query->where('id', $employeeId);
            }
            // Apply search if provided
            else if (!empty($searchQuery)) {
                $query->where(function($q) use ($searchQuery) {
                    $q->where('first_name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('email', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('phone_number_1', 'LIKE', "%{$searchQuery}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchQuery}%"]);
                });
            }

            $employees = $query->limit($limit)->get();

            // Format for dropdown
            $data = $employees->map(function($employee) {
                $fullName = trim("{$employee->first_name} {$employee->last_name}");
                $email = $employee->email ?: '';
                $phone = $employee->phone_number_1 ?: '';
                $userType = $employee->user_type ?: 'Employee';

                // Create detailed text with available info
                $contactInfo = [];
                if ($email) $contactInfo[] = $email;
                if ($phone) $contactInfo[] = $phone;
                
                $contactText = !empty($contactInfo) ? ' (' . implode(', ', $contactInfo) . ')' : '';
                $displayText = "{$fullName} - {$userType}{$contactText}";

                return [
                    'id' => $employee->id,
                    'text' => $displayText,
                    'name' => $fullName,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'user_type' => $userType,
                    'details' => "{$fullName} | {$userType} | {$email} | {$phone}"
                ];
            });

            return $this->success($data->values()->all(), 'Employees retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Error retrieving employees: ' . $e->getMessage());
        }
    }

    /**
     * Get stock items for dropdown (for service types)
     */
    public function getStockItemsForDropdown(Request $request)
    {
        try {
            $search = $request->get('q', '');
            $limit = min(50, max(10, $request->get('limit', 20)));

            $query = StockItem::query()
                ->where('current_quantity', '>', 0) // Only items in stock
                ->orderBy('name', 'asc');

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('category', 'LIKE', "%{$search}%");
                });
            }

            $stockItems = $query->limit($limit)->get();

            // Remove duplicates and format data
            $data = $stockItems->unique('name')->map(function ($item) {
                $displayText = $item->name;
                if (!empty($item->category)) {
                    $displayText .= " ({$item->category})";
                }
                if ($item->current_quantity > 0) {
                    $displayText .= " - Stock: {$item->current_quantity}";
                }

                return [
                    'id' => $item->id,
                    'text' => $displayText,
                    'name' => $item->name,
                    'category' => $item->category,
                    'description' => $item->description,
                    'current_quantity' => $item->current_quantity,
                    'unit_price' => $item->unit_price,
                ];
            });

            return $this->success($data->values()->all(), 'Stock items retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Error retrieving stock items: ' . $e->getMessage());
        }
    }
}