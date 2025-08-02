<?php

namespace App\Http\Requests;

/**
 * MedicalServiceRequest - Handles medical service form validation
 * 
 * Validates medical service information with clinical requirements,
 * enterprise scoping, and service management features
 */
class MedicalServiceRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return parent::authorize() && $this->authorizeEnterprise();
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        $serviceId = $this->route('medical_service') ?? $this->route('service');
        
        return array_merge([
            // Basic Information
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:medical_services,name,' . $serviceId . ',id,enterprise_id,' . auth()->user()->enterprise_id
            ],
            'description' => 'nullable|string|max:2000',
            'short_description' => 'nullable|string|max:500',
            'service_code' => [
                'required',
                'string',
                'max:50',
                'unique:medical_services,service_code,' . $serviceId . ',id,enterprise_id,' . auth()->user()->enterprise_id
            ],

            // Classification
            'category' => 'required|in:Consultation,Diagnostic,Therapeutic,Surgical,Laboratory,Imaging,Emergency,Preventive,Rehabilitation,Other',
            'specialty' => 'nullable|in:General Medicine,Cardiology,Neurology,Orthopedics,Pediatrics,Gynecology,Dermatology,Psychiatry,Radiology,Pathology,Anesthesiology,Surgery,Emergency Medicine,Other',
            'department_id' => [
                'nullable',
                'exists:departments,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $department = \App\Models\Department::find($value);
                        if ($department && $department->enterprise_id !== auth()->user()->enterprise_id) {
                            $fail('The selected department does not belong to your enterprise.');
                        }
                    }
                }
            ],
            'sub_category' => 'nullable|string|max:100',

            // Pricing Information
            'base_price' => 'required|numeric|min:0|max:9999999.99',
            'minimum_price' => 'nullable|numeric|min:0',
            'maximum_price' => 'nullable|numeric|min:0',
            'insurance_price' => 'nullable|numeric|min:0|max:9999999.99',
            'emergency_price' => 'nullable|numeric|min:0|max:9999999.99',
            'discount_eligible' => 'nullable|boolean',
            'tax_applicable' => 'nullable|boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',

            // Service Details
            'duration_minutes' => 'nullable|integer|min:1|max:1440', // Max 24 hours
            'preparation_time' => 'nullable|integer|min:0|max:240', // Max 4 hours
            'recovery_time' => 'nullable|integer|min:0|max:10080', // Max 1 week
            'requires_appointment' => 'nullable|boolean',
            'requires_referral' => 'nullable|boolean',
            'requires_preparation' => 'nullable|boolean',
            'requires_consent' => 'nullable|boolean',

            // Clinical Information
            'contraindications' => 'nullable|string|max:2000',
            'precautions' => 'nullable|string|max:2000',
            'side_effects' => 'nullable|string|max:2000',
            'preparation_instructions' => 'nullable|string|max:2000',
            'post_procedure_instructions' => 'nullable|string|max:2000',

            // Resource Requirements
            'equipment_required' => 'nullable|string|max:1000',
            'room_type_required' => 'nullable|in:Consultation Room,Procedure Room,Operating Theater,ICU,Emergency Room,Laboratory,Imaging Room,Other',
            'staff_required' => 'nullable|string|max:500',
            'consumables_required' => 'nullable|string|max:1000',

            // Availability and Scheduling
            'available_days' => 'nullable|json',
            'available_times' => 'nullable|json',
            'max_daily_capacity' => 'nullable|integer|min:1|max:1000',
            'advance_booking_days' => 'nullable|integer|min:0|max:365',
            'cancellation_hours' => 'nullable|integer|min:0|max:168', // Max 1 week

            // Insurance and Billing
            'insurance_coverage' => 'nullable|in:Full,Partial,None',
            'coverage_percentage' => 'nullable|numeric|min:0|max:100',
            'copay_amount' => 'nullable|numeric|min:0|max:9999.99',
            'billing_code' => 'nullable|string|max:50',
            'cpt_code' => 'nullable|string|max:20',
            'icd_codes' => 'nullable|json',

            // Quality and Safety
            'risk_level' => 'nullable|in:Low,Medium,High,Critical',
            'quality_metrics' => 'nullable|json',
            'safety_checklist' => 'nullable|json',
            'outcome_measures' => 'nullable|string|max:1000',

            // Status and Flags
            'status' => 'required|in:Active,Inactive,Discontinued,Under Review',
            'is_emergency_service' => 'nullable|boolean',
            'is_telemedicine_available' => 'nullable|boolean',
            'is_home_service_available' => 'nullable|boolean',
            'requires_license' => 'nullable|boolean',

            // Metadata
            'tags' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:1000',
            'version' => 'nullable|string|max:20',
            'effective_date' => 'nullable|date|after_or_equal:today',
            'expiry_date' => 'nullable|date|after:effective_date',
        ], $this->medicalRules(), $this->financialRules());
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.required' => 'Service name is required.',
            'name.unique' => 'A service with this name already exists in your enterprise.',
            'service_code.required' => 'Service code is required.',
            'service_code.unique' => 'This service code is already in use in your enterprise.',
            'category.required' => 'Service category is required.',
            'category.in' => 'Please select a valid service category.',
            'specialty.in' => 'Please select a valid medical specialty.',
            'base_price.required' => 'Base price is required for all services.',
            'base_price.numeric' => 'Base price must be a valid amount.',
            'base_price.min' => 'Base price cannot be negative.',
            'base_price.max' => 'Base price amount is too large.',
            'minimum_price.min' => 'Minimum price cannot be negative.',
            'maximum_price.min' => 'Maximum price cannot be negative.',
            'insurance_price.max' => 'Insurance price amount is too large.',
            'emergency_price.max' => 'Emergency price amount is too large.',
            'tax_rate.max' => 'Tax rate cannot exceed 100%.',
            'duration_minutes.min' => 'Duration must be at least 1 minute.',
            'duration_minutes.max' => 'Duration cannot exceed 24 hours.',
            'preparation_time.max' => 'Preparation time cannot exceed 4 hours.',
            'recovery_time.max' => 'Recovery time cannot exceed 1 week.',
            'room_type_required.in' => 'Please select a valid room type.',
            'available_days.json' => 'Available days must be valid JSON format.',
            'available_times.json' => 'Available times must be valid JSON format.',
            'max_daily_capacity.min' => 'Daily capacity must be at least 1.',
            'max_daily_capacity.max' => 'Daily capacity cannot exceed 1000.',
            'advance_booking_days.max' => 'Advance booking cannot exceed 365 days.',
            'cancellation_hours.max' => 'Cancellation period cannot exceed 1 week.',
            'insurance_coverage.in' => 'Please select a valid insurance coverage type.',
            'coverage_percentage.max' => 'Coverage percentage cannot exceed 100%.',
            'risk_level.in' => 'Please select a valid risk level.',
            'status.required' => 'Service status is required.',
            'status.in' => 'Please select a valid service status.',
            'effective_date.after_or_equal' => 'Effective date cannot be in the past.',
            'expiry_date.after' => 'Expiry date must be after the effective date.',
        ]);
    }

    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'service_code' => 'service code',
            'short_description' => 'short description',
            'sub_category' => 'sub category',
            'department_id' => 'department',
            'base_price' => 'base price',
            'minimum_price' => 'minimum price',
            'maximum_price' => 'maximum price',
            'insurance_price' => 'insurance price',
            'emergency_price' => 'emergency price',
            'discount_eligible' => 'discount eligible',
            'tax_applicable' => 'tax applicable',
            'tax_rate' => 'tax rate',
            'duration_minutes' => 'duration',
            'preparation_time' => 'preparation time',
            'recovery_time' => 'recovery time',
            'requires_appointment' => 'requires appointment',
            'requires_referral' => 'requires referral',
            'requires_preparation' => 'requires preparation',
            'requires_consent' => 'requires consent',
            'preparation_instructions' => 'preparation instructions',
            'post_procedure_instructions' => 'post-procedure instructions',
            'equipment_required' => 'equipment required',
            'room_type_required' => 'room type required',
            'staff_required' => 'staff required',
            'consumables_required' => 'consumables required',
            'available_days' => 'available days',
            'available_times' => 'available times',
            'max_daily_capacity' => 'maximum daily capacity',
            'advance_booking_days' => 'advance booking days',
            'cancellation_hours' => 'cancellation hours',
            'insurance_coverage' => 'insurance coverage',
            'coverage_percentage' => 'coverage percentage',
            'copay_amount' => 'copay amount',
            'billing_code' => 'billing code',
            'cpt_code' => 'CPT code',
            'icd_codes' => 'ICD codes',
            'risk_level' => 'risk level',
            'quality_metrics' => 'quality metrics',
            'safety_checklist' => 'safety checklist',
            'outcome_measures' => 'outcome measures',
            'is_emergency_service' => 'emergency service',
            'is_telemedicine_available' => 'telemedicine available',
            'is_home_service_available' => 'home service available',
            'requires_license' => 'requires license',
            'internal_notes' => 'internal notes',
            'effective_date' => 'effective date',
            'expiry_date' => 'expiry date',
        ]);
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Auto-generate service code if not provided
        if (!$this->has('service_code') || empty($this->input('service_code'))) {
            $this->merge(['service_code' => $this->generateServiceCode()]);
        }

        // Set default pricing values
        if ($this->has('base_price')) {
            if (!$this->has('minimum_price') || empty($this->input('minimum_price'))) {
                $this->merge(['minimum_price' => $this->input('base_price') * 0.8]);
            }

            if (!$this->has('maximum_price') || empty($this->input('maximum_price'))) {
                $this->merge(['maximum_price' => $this->input('base_price') * 1.5]);
            }

            if (!$this->has('insurance_price') || empty($this->input('insurance_price'))) {
                $this->merge(['insurance_price' => $this->input('base_price') * 0.9]);
            }

            if (!$this->has('emergency_price') || empty($this->input('emergency_price'))) {
                $this->merge(['emergency_price' => $this->input('base_price') * 1.5]);
            }
        }

        // Set default boolean values
        $booleanFields = [
            'discount_eligible' => true,
            'tax_applicable' => true,
            'requires_appointment' => true,
            'requires_referral' => false,
            'requires_preparation' => false,
            'requires_consent' => false,
            'is_emergency_service' => false,
            'is_telemedicine_available' => false,
            'is_home_service_available' => false,
            'requires_license' => false,
        ];

        foreach ($booleanFields as $field => $default) {
            if (!$this->has($field)) {
                $this->merge([$field => $default]);
            }
        }

        // Set default status
        if (!$this->has('status') || empty($this->input('status'))) {
            $this->merge(['status' => 'Active']);
        }

        // Set default tax rate if tax is applicable
        if ($this->input('tax_applicable') && (!$this->has('tax_rate') || empty($this->input('tax_rate')))) {
            $this->merge(['tax_rate' => 5.0]); // Default 5% tax
        }

        // Set default duration for different categories
        if (!$this->has('duration_minutes') || empty($this->input('duration_minutes'))) {
            $defaultDurations = [
                'Consultation' => 30,
                'Diagnostic' => 45,
                'Therapeutic' => 60,
                'Surgical' => 120,
                'Laboratory' => 15,
                'Imaging' => 30,
                'Emergency' => 60,
                'Preventive' => 30,
                'Rehabilitation' => 45,
                'Other' => 30,
            ];

            $category = $this->input('category');
            if (isset($defaultDurations[$category])) {
                $this->merge(['duration_minutes' => $defaultDurations[$category]]);
            }
        }

        // Set default available days (Monday to Friday)
        if (!$this->has('available_days') || empty($this->input('available_days'))) {
            $this->merge(['available_days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])]);
        }

        // Set default available times (9 AM to 5 PM)
        if (!$this->has('available_times') || empty($this->input('available_times'))) {
            $this->merge(['available_times' => json_encode(['start' => '09:00', 'end' => '17:00'])]);
        }

        // Set default risk level based on category
        if (!$this->has('risk_level') || empty($this->input('risk_level'))) {
            $riskLevels = [
                'Consultation' => 'Low',
                'Diagnostic' => 'Low',
                'Therapeutic' => 'Medium',
                'Surgical' => 'High',
                'Laboratory' => 'Low',
                'Imaging' => 'Low',
                'Emergency' => 'High',
                'Preventive' => 'Low',
                'Rehabilitation' => 'Low',
                'Other' => 'Medium',
            ];

            $category = $this->input('category');
            if (isset($riskLevels[$category])) {
                $this->merge(['risk_level' => $riskLevels[$category]]);
            }
        }
    }

    /**
     * Generate unique service code
     */
    private function generateServiceCode(): string
    {
        $category = $this->input('category', 'SVC');
        $prefix = strtoupper(substr($category, 0, 3));
        $lastNumber = \App\Models\MedicalService::where('service_code', 'like', $prefix . '%')
            ->where('enterprise_id', auth()->user()->enterprise_id)
            ->count() + 1;

        return $prefix . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Additional validation logic for complex business rules
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate price relationships
            if ($this->filled(['minimum_price', 'base_price'])) {
                if ($this->input('minimum_price') > $this->input('base_price')) {
                    $validator->errors()->add('minimum_price', 
                        'Minimum price cannot exceed base price.');
                }
            }

            if ($this->filled(['maximum_price', 'base_price'])) {
                if ($this->input('maximum_price') < $this->input('base_price')) {
                    $validator->errors()->add('maximum_price', 
                        'Maximum price cannot be less than base price.');
                }
            }

            // Validate tax rate when tax is applicable
            if ($this->input('tax_applicable') && !$this->filled('tax_rate')) {
                $validator->errors()->add('tax_rate', 
                    'Tax rate is required when tax is applicable.');
            }

            // Validate coverage percentage when insurance coverage is partial
            if ($this->input('insurance_coverage') === 'Partial' && !$this->filled('coverage_percentage')) {
                $validator->errors()->add('coverage_percentage', 
                    'Coverage percentage is required for partial insurance coverage.');
            }

            // Validate JSON fields
            $jsonFields = ['available_days', 'available_times', 'icd_codes', 'quality_metrics', 'safety_checklist'];
            foreach ($jsonFields as $field) {
                if ($this->filled($field)) {
                    $value = $this->input($field);
                    if (is_string($value) && !json_decode($value)) {
                        $validator->errors()->add($field, 
                            ucfirst(str_replace('_', ' ', $field)) . ' must be valid JSON.');
                    }
                }
            }

            // Validate capacity for appointment-based services
            if ($this->input('requires_appointment') && !$this->filled('max_daily_capacity')) {
                $validator->errors()->add('max_daily_capacity', 
                    'Daily capacity is required for appointment-based services.');
            }

            // Validate preparation time for services requiring preparation
            if ($this->input('requires_preparation') && !$this->filled('preparation_instructions')) {
                $validator->errors()->add('preparation_instructions', 
                    'Preparation instructions are required for services requiring preparation.');
            }

            // Validate consent requirements for high-risk procedures
            if ($this->input('risk_level') === 'High' && !$this->input('requires_consent')) {
                $validator->errors()->add('requires_consent', 
                    'Consent is required for high-risk procedures.');
            }

            // Validate effective and expiry dates
            if ($this->filled(['effective_date', 'expiry_date'])) {
                if ($this->input('expiry_date') <= $this->input('effective_date')) {
                    $validator->errors()->add('expiry_date', 
                        'Expiry date must be after the effective date.');
                }
            }

            // Validate emergency service pricing
            if ($this->input('is_emergency_service') && !$this->filled('emergency_price')) {
                $validator->errors()->add('emergency_price', 
                    'Emergency price is required for emergency services.');
            }
        });
    }
}
