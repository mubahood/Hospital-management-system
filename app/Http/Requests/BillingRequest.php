<?php

namespace App\Http\Requests;

/**
 * BillingRequest - Handles billing item form validation
 * 
 * Validates billing and payment information with financial rules,
 * enterprise scoping, and comprehensive data validation
 */
class BillingRequest extends BaseRequest
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
        return array_merge([
            // Required Relations
            'consultation_id' => [
                'required',
                'exists:consultations,id',
                function ($attribute, $value, $fail) {
                    $consultation = \App\Models\Consultation::find($value);
                    if ($consultation && $consultation->enterprise_id !== auth()->user()->enterprise_id) {
                        $fail('The selected consultation does not belong to your enterprise.');
                    }
                }
            ],
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

            // Billing Information
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'billing_type' => 'required|in:Service,Medication,Procedure,Consultation,Laboratory,Imaging,Emergency,Other',
            'category' => 'nullable|string|max:100',

            // Financial Fields
            'price' => 'required|numeric|min:0|max:9999999.99',
            'quantity' => 'required|integer|min:1|max:10000',
            'total' => 'nullable|numeric|min:0|max:9999999.99',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'nullable|numeric|min:0|max:9999999.99',

            // Status and Tracking
            'status' => 'required|in:Pending,Approved,Cancelled,Refunded',
            'payment_status' => 'nullable|in:Pending,Partial,Paid,Overdue,Cancelled',
            'due_date' => 'nullable|date|after_or_equal:today',
            'billing_date' => 'required|date|before_or_equal:today',

            // Reference Information
            'invoice_number' => 'nullable|string|max:50|unique:billing_items,invoice_number,' . $this->route('billing_item'),
            'reference_number' => 'nullable|string|max:100',
            'external_reference' => 'nullable|string|max:100',

            // Medical Service Link
            'medical_service_id' => [
                'nullable',
                'exists:medical_services,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $service = \App\Models\MedicalService::find($value);
                        if ($service && $service->enterprise_id !== auth()->user()->enterprise_id) {
                            $fail('The selected medical service does not belong to your enterprise.');
                        }
                    }
                }
            ],

            // Additional Information
            'notes' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:1000',
            'priority' => 'nullable|in:Low,Normal,High,Urgent',
        ], $this->financialRules());
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'consultation_id.required' => 'Consultation is required for billing items.',
            'consultation_id.exists' => 'The selected consultation is invalid.',
            'patient_id.required' => 'Patient is required for billing items.',
            'patient_id.exists' => 'The selected patient is invalid.',
            'name.required' => 'Billing item name is required.',
            'name.max' => 'Billing item name should not exceed 255 characters.',
            'billing_type.required' => 'Billing type is required.',
            'billing_type.in' => 'Invalid billing type selected.',
            'price.required' => 'Price is required for all billing items.',
            'price.numeric' => 'Price must be a valid amount.',
            'price.min' => 'Price cannot be negative.',
            'price.max' => 'Price amount is too large.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity cannot exceed 10,000.',
            'discount_percentage.max' => 'Discount percentage cannot exceed 100%.',
            'tax_rate.max' => 'Tax rate cannot exceed 100%.',
            'status.required' => 'Billing status is required.',
            'status.in' => 'Invalid billing status selected.',
            'payment_status.in' => 'Invalid payment status selected.',
            'due_date.after_or_equal' => 'Due date cannot be in the past.',
            'billing_date.required' => 'Billing date is required.',
            'billing_date.before_or_equal' => 'Billing date cannot be in the future.',
            'invoice_number.unique' => 'This invoice number is already in use.',
        ]);
    }

    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'consultation_id' => 'consultation',
            'patient_id' => 'patient',
            'medical_service_id' => 'medical service',
            'billing_type' => 'billing type',
            'discount_percentage' => 'discount percentage',
            'discount_amount' => 'discount amount',
            'tax_rate' => 'tax rate',
            'tax_amount' => 'tax amount',
            'final_amount' => 'final amount',
            'payment_status' => 'payment status',
            'due_date' => 'due date',
            'billing_date' => 'billing date',
            'invoice_number' => 'invoice number',
            'reference_number' => 'reference number',
            'external_reference' => 'external reference',
            'internal_notes' => 'internal notes',
        ]);
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Auto-calculate total if not provided
        if ($this->has('price') && $this->has('quantity') && 
            (!$this->has('total') || empty($this->input('total')))) {
            $total = $this->input('price') * $this->input('quantity');
            $this->merge(['total' => round($total, 2)]);
        }

        // Auto-calculate discount amount if percentage is provided
        if ($this->has('discount_percentage') && $this->has('total') && 
            !empty($this->input('discount_percentage')) &&
            (!$this->has('discount_amount') || empty($this->input('discount_amount')))) {
            $discountAmount = ($this->input('total') * $this->input('discount_percentage')) / 100;
            $this->merge(['discount_amount' => round($discountAmount, 2)]);
        }

        // Auto-calculate tax amount if rate is provided
        if ($this->has('tax_rate') && $this->has('total') && 
            !empty($this->input('tax_rate')) &&
            (!$this->has('tax_amount') || empty($this->input('tax_amount')))) {
            $totalAfterDiscount = $this->input('total') - ($this->input('discount_amount') ?? 0);
            $taxAmount = ($totalAfterDiscount * $this->input('tax_rate')) / 100;
            $this->merge(['tax_amount' => round($taxAmount, 2)]);
        }

        // Auto-calculate final amount
        if ($this->has('total')) {
            $finalAmount = $this->input('total') 
                - ($this->input('discount_amount') ?? 0) 
                + ($this->input('tax_amount') ?? 0);
            $this->merge(['final_amount' => round($finalAmount, 2)]);
        }

        // Auto-generate invoice number if not provided
        if (!$this->has('invoice_number') || empty($this->input('invoice_number'))) {
            $this->merge(['invoice_number' => $this->generateInvoiceNumber()]);
        }

        // Set default billing date if not provided
        if (!$this->has('billing_date') || empty($this->input('billing_date'))) {
            $this->merge(['billing_date' => now()->format('Y-m-d')]);
        }

        // Set default status if not provided
        if (!$this->has('status') || empty($this->input('status'))) {
            $this->merge(['status' => 'Pending']);
        }

        // Set default payment status if not provided
        if (!$this->has('payment_status') || empty($this->input('payment_status'))) {
            $this->merge(['payment_status' => 'Pending']);
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastNumber = \App\Models\BillingItem::where('invoice_number', 'like', $prefix . $date . '%')
            ->count() + 1;

        return $prefix . $date . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Additional validation logic for complex business rules
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate total calculation
            if ($this->filled(['price', 'quantity', 'total'])) {
                $calculatedTotal = $this->input('price') * $this->input('quantity');
                $providedTotal = (float) $this->input('total');
                
                if (abs($calculatedTotal - $providedTotal) > 0.01) {
                    $validator->errors()->add('total', 
                        'Total amount does not match price × quantity calculation.');
                }
            }

            // Validate discount amount vs percentage
            if ($this->filled(['discount_percentage', 'discount_amount', 'total'])) {
                $calculatedDiscount = ($this->input('total') * $this->input('discount_percentage')) / 100;
                $providedDiscount = (float) $this->input('discount_amount');
                
                if (abs($calculatedDiscount - $providedDiscount) > 0.01) {
                    $validator->errors()->add('discount_amount', 
                        'Discount amount does not match the percentage calculation.');
                }
            }

            // Validate tax amount calculation
            if ($this->filled(['tax_rate', 'tax_amount', 'total'])) {
                $totalAfterDiscount = $this->input('total') - ($this->input('discount_amount') ?? 0);
                $calculatedTax = ($totalAfterDiscount * $this->input('tax_rate')) / 100;
                $providedTax = (float) $this->input('tax_amount');
                
                if (abs($calculatedTax - $providedTax) > 0.01) {
                    $validator->errors()->add('tax_amount', 
                        'Tax amount does not match the rate calculation.');
                }
            }

            // Validate final amount calculation
            if ($this->filled(['total', 'final_amount'])) {
                $calculatedFinal = $this->input('total') 
                    - ($this->input('discount_amount') ?? 0) 
                    + ($this->input('tax_amount') ?? 0);
                $providedFinal = (float) $this->input('final_amount');
                
                if (abs($calculatedFinal - $providedFinal) > 0.01) {
                    $validator->errors()->add('final_amount', 
                        'Final amount calculation is incorrect.');
                }
            }

            // Validate that discount amount doesn't exceed total
            if ($this->filled(['discount_amount', 'total'])) {
                if ($this->input('discount_amount') > $this->input('total')) {
                    $validator->errors()->add('discount_amount', 
                        'Discount amount cannot exceed the total amount.');
                }
            }

            // Validate patient belongs to the same consultation
            if ($this->filled(['consultation_id', 'patient_id'])) {
                $consultation = \App\Models\Consultation::find($this->input('consultation_id'));
                if ($consultation && $consultation->patient_id != $this->input('patient_id')) {
                    $validator->errors()->add('patient_id', 
                        'Patient must match the consultation patient.');
                }
            }
        });
    }
}
