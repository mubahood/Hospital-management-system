<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * ConsultationRequest - Handles consultation form validation
 * 
 * Validates all consultation-related input with medical-specific rules,
 * enterprise scoping, and comprehensive data sanitization
 */
class ConsultationRequest extends BaseRequest
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
        $rules = [
            // Patient Information
            'patient_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $patient = \App\Models\User::find($value);
                    if ($patient && $patient->enterprise_id !== auth()->user()->enterprise_id) {
                        $fail('The selected patient does not belong to your enterprise.');
                    }
                }
            ],
            
            // Doctor Assignment
            'assigned_to' => [
                'nullable',
                'exists:admin_users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $doctor = \Encore\Admin\Auth\Database\Administrator::find($value);
                        if ($doctor && isset($doctor->enterprise_id) && $doctor->enterprise_id !== auth()->user()->enterprise_id) {
                            $fail('The selected doctor does not belong to your enterprise.');
                        }
                    }
                }
            ],

            // Department
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

            // Consultation Details
            'consultation_number' => 'nullable|string|max:50|unique:consultations,consultation_number,' . $this->route('consultation'),
            'chief_complaint' => 'required|string|max:500',
            'history_of_present_illness' => 'nullable|string|max:2000',
            'medical_history' => 'nullable|string|max:2000',
            'family_history' => 'nullable|string|max:1000',
            'social_history' => 'nullable|string|max:1000',
            'allergies' => 'nullable|string|max:1000',
            'current_medications' => 'nullable|string|max:1000',

            // Physical Examination
            'general_appearance' => 'nullable|string|max:500',
            'physical_examination' => 'nullable|string|max:2000',
            'systems_review' => 'nullable|string|max:2000',

            // Diagnosis and Treatment
            'diagnosis' => 'nullable|string|max:1000',
            'differential_diagnosis' => 'nullable|string|max:1000',
            'treatment_plan' => 'nullable|string|max:2000',
            'prescriptions' => 'nullable|string|max:2000',
            'follow_up_instructions' => 'nullable|string|max:1000',
            'referrals' => 'nullable|string|max:1000',

            // Status and Dates
            'status' => 'required|in:Scheduled,In Progress,Completed,Cancelled',
            'consultation_date_time' => 'required|date|after_or_equal:yesterday',
            'consultation_end_date_time' => 'nullable|date|after:consultation_date_time',

            // Financial Information
            'consultation_fee' => 'nullable|numeric|min:0|max:999999.99',
            'total_amount' => 'nullable|numeric|min:0|max:999999.99',
            'payment_status' => 'nullable|in:Pending,Partial,Paid,Refunded',

            // Additional Information
            'notes' => 'nullable|string|max:2000',
            'summary' => 'nullable|string|max:1000',
            'recommendations' => 'nullable|string|max:1000',
            'priority' => 'nullable|in:Low,Normal,High,Urgent',
            'consultation_type' => 'nullable|in:Initial,Follow-up,Emergency,Routine',
        ];

        // Merge with medical vital signs rules
        return array_merge($rules, $this->medicalRules());
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'patient_id.required' => 'Please select a patient for this consultation.',
            'patient_id.exists' => 'The selected patient is invalid.',
            'chief_complaint.required' => 'Chief complaint is required for every consultation.',
            'chief_complaint.max' => 'Chief complaint should not exceed 500 characters.',
            'consultation_date_time.required' => 'Consultation date and time is required.',
            'consultation_date_time.after_or_equal' => 'Consultation date cannot be in the past.',
            'consultation_end_date_time.after' => 'End time must be after the start time.',
            'status.required' => 'Consultation status is required.',
            'status.in' => 'Invalid consultation status selected.',
            'consultation_fee.numeric' => 'Consultation fee must be a valid amount.',
            'consultation_fee.min' => 'Consultation fee cannot be negative.',
            'blood_pressure_systolic.min' => 'Systolic pressure seems too low (minimum 50).',
            'blood_pressure_systolic.max' => 'Systolic pressure seems too high (maximum 300).',
            'blood_pressure_diastolic.min' => 'Diastolic pressure seems too low (minimum 30).',
            'blood_pressure_diastolic.max' => 'Diastolic pressure seems too high (maximum 200).',
            'heart_rate.min' => 'Heart rate seems too low (minimum 30 bpm).',
            'heart_rate.max' => 'Heart rate seems too high (maximum 220 bpm).',
            'temperature.min' => 'Temperature seems too low (minimum 30°C).',
            'temperature.max' => 'Temperature seems too high (maximum 45°C).',
            'weight.min' => 'Weight seems too low (minimum 0.5 kg).',
            'weight.max' => 'Weight seems too high (maximum 500 kg).',
            'height.min' => 'Height seems too low (minimum 30 cm).',
            'height.max' => 'Height seems too high (maximum 250 cm).',
        ]);
    }

    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'patient_id' => 'patient',
            'assigned_to' => 'assigned doctor',
            'department_id' => 'department',
            'chief_complaint' => 'chief complaint',
            'history_of_present_illness' => 'history of present illness',
            'medical_history' => 'medical history',
            'family_history' => 'family history',
            'social_history' => 'social history',
            'current_medications' => 'current medications',
            'general_appearance' => 'general appearance',
            'physical_examination' => 'physical examination',
            'systems_review' => 'systems review',
            'differential_diagnosis' => 'differential diagnosis',
            'treatment_plan' => 'treatment plan',
            'follow_up_instructions' => 'follow-up instructions',
            'consultation_date_time' => 'consultation date and time',
            'consultation_end_date_time' => 'consultation end time',
            'consultation_fee' => 'consultation fee',
            'total_amount' => 'total amount',
            'payment_status' => 'payment status',
            'consultation_type' => 'consultation type',
            'blood_pressure_systolic' => 'systolic blood pressure',
            'blood_pressure_diastolic' => 'diastolic blood pressure',
            'heart_rate' => 'heart rate',
        ]);
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Auto-generate consultation number if not provided
        if (!$this->has('consultation_number') || empty($this->input('consultation_number'))) {
            $this->merge([
                'consultation_number' => $this->generateConsultationNumber()
            ]);
        }

        // Auto-calculate BMI if height and weight are provided
        if ($this->has('height') && $this->has('weight') && 
            !empty($this->input('height')) && !empty($this->input('weight'))) {
            $this->merge([
                'bmi' => $this->calculateBMI($this->input('weight'), $this->input('height'))
            ]);
        }

        // Set default status if not provided
        if (!$this->has('status') || empty($this->input('status'))) {
            $this->merge([
                'status' => 'Scheduled'
            ]);
        }

        // Set default payment status if consultation fee is provided
        if ($this->has('consultation_fee') && !empty($this->input('consultation_fee')) && 
            (!$this->has('payment_status') || empty($this->input('payment_status')))) {
            $this->merge([
                'payment_status' => 'Pending'
            ]);
        }
    }

    /**
     * Generate unique consultation number
     */
    private function generateConsultationNumber(): string
    {
        $prefix = 'CON';
        $date = now()->format('Ymd');
        $lastNumber = \App\Models\Consultation::where('consultation_number', 'like', $prefix . $date . '%')
            ->count() + 1;

        return $prefix . $date . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate BMI from weight and height
     */
    private function calculateBMI(float $weight, float $height): float
    {
        // Convert height from cm to meters if necessary
        if ($height > 3) {
            $height = $height / 100;
        }

        return round($weight / ($height * $height), 2);
    }

    /**
     * Additional validation logic for complex business rules
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that end time is after start time if both are provided
            if ($this->filled('consultation_date_time') && $this->filled('consultation_end_date_time')) {
                $startTime = \Carbon\Carbon::parse($this->input('consultation_date_time'));
                $endTime = \Carbon\Carbon::parse($this->input('consultation_end_date_time'));
                
                if ($endTime->lessThanOrEqualTo($startTime)) {
                    $validator->errors()->add('consultation_end_date_time', 
                        'End time must be after the start time.');
                }
            }

            // Validate blood pressure combination if provided
            if ($this->filled('blood_pressure_systolic') && $this->filled('blood_pressure_diastolic')) {
                $systolic = (int) $this->input('blood_pressure_systolic');
                $diastolic = (int) $this->input('blood_pressure_diastolic');
                
                if ($systolic <= $diastolic) {
                    $validator->errors()->add('blood_pressure_systolic', 
                        'Systolic pressure must be higher than diastolic pressure.');
                }
            }

            // Validate BMI calculation if auto-calculated
            if ($this->filled(['height', 'weight', 'bmi'])) {
                $calculatedBMI = $this->calculateBMI($this->input('weight'), $this->input('height'));
                $providedBMI = (float) $this->input('bmi');
                
                if (abs($calculatedBMI - $providedBMI) > 0.1) {
                    $validator->errors()->add('bmi', 
                        'BMI calculation does not match provided height and weight.');
                }
            }
        });
    }
}
