<?php

namespace App\Http\Requests;

/**
 * PaymentRequest - Handles payment processing form validation
 * 
 * Validates payment information with financial security,
 * enterprise scoping, and payment gateway integration
 */
class PaymentRequest extends BaseRequest
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
            'billing_item_id' => [
                'required',
                'exists:billing_items,id',
                function ($attribute, $value, $fail) {
                    $billingItem = \App\Models\BillingItem::find($value);
                    if ($billingItem && $billingItem->enterprise_id !== auth()->user()->enterprise_id) {
                        $fail('The selected billing item does not belong to your enterprise.');
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

            // Payment Information
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'payment_method' => 'required|in:Cash,Credit Card,Debit Card,Bank Transfer,Check,Insurance,Mobile Payment,Online Payment,Other',
            'payment_type' => 'required|in:Full Payment,Partial Payment,Advance Payment,Refund,Adjustment',
            'payment_status' => 'required|in:Pending,Processing,Completed,Failed,Cancelled,Refunded',
            'currency' => 'required|string|size:3|in:USD,EUR,GBP,NGN,GHS,KES,UGX,TZS,ZAR',

            // Transaction Details
            'transaction_id' => 'nullable|string|max:100|unique:payment_records,transaction_id,' . $this->route('payment'),
            'reference_number' => 'nullable|string|max:100',
            'gateway_reference' => 'nullable|string|max:100',
            'authorization_code' => 'nullable|string|max:50',
            'receipt_number' => 'nullable|string|max:50|unique:payment_records,receipt_number,' . $this->route('payment'),

            // Payment Gateway Information
            'gateway_name' => 'nullable|in:Stripe,PayPal,Flutterwave,Paystack,Square,Razorpay,Manual,Other',
            'gateway_response' => 'nullable|json',
            'gateway_fee' => 'nullable|numeric|min:0|max:99999.99',
            'gateway_status' => 'nullable|string|max:50',

            // Card Information (if applicable)
            'card_last_four' => 'nullable|string|size:4|regex:/^[0-9]{4}$/',
            'card_type' => 'nullable|in:Visa,MasterCard,American Express,Discover,JCB,Diners Club,Other',
            'card_expiry_month' => 'nullable|integer|min:1|max:12',
            'card_expiry_year' => 'nullable|integer|min:' . date('Y') . '|max:' . (date('Y') + 20),

            // Bank Information (if applicable)
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'routing_number' => 'nullable|string|max:20',
            'check_number' => 'nullable|string|max:20',

            // Dates
            'payment_date' => 'required|date|before_or_equal:today',
            'due_date' => 'nullable|date',
            'processed_at' => 'nullable|date',

            // Additional Information
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'internal_notes' => 'nullable|string|max:500',
            'tags' => 'nullable|string|max:200',

            // Status and Flags
            'is_recurring' => 'nullable|boolean',
            'is_refundable' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'requires_approval' => 'nullable|boolean',

            // Fee and Tax Information
            'processing_fee' => 'nullable|numeric|min:0|max:9999.99',
            'tax_amount' => 'nullable|numeric|min:0|max:9999.99',
            'discount_amount' => 'nullable|numeric|min:0|max:9999.99',
            'net_amount' => 'nullable|numeric|min:0|max:9999999.99',

            // Payer Information
            'payer_name' => 'nullable|string|max:100',
            'payer_email' => 'nullable|email|max:255',
            'payer_phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'payer_address' => 'nullable|string|max:500',
        ], $this->financialRules());
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'billing_item_id.required' => 'Billing item is required for payment processing.',
            'billing_item_id.exists' => 'The selected billing item is invalid.',
            'patient_id.required' => 'Patient is required for payment processing.',
            'patient_id.exists' => 'The selected patient is invalid.',
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'amount.min' => 'Payment amount must be greater than zero.',
            'amount.max' => 'Payment amount is too large.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Invalid payment method selected.',
            'payment_type.required' => 'Payment type is required.',
            'payment_type.in' => 'Invalid payment type selected.',
            'payment_status.required' => 'Payment status is required.',
            'payment_status.in' => 'Invalid payment status selected.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be a 3-letter code.',
            'currency.in' => 'Unsupported currency selected.',
            'transaction_id.unique' => 'This transaction ID already exists.',
            'receipt_number.unique' => 'This receipt number is already in use.',
            'gateway_name.in' => 'Invalid payment gateway selected.',
            'gateway_response.json' => 'Gateway response must be valid JSON.',
            'card_last_four.size' => 'Card last four digits must be exactly 4 numbers.',
            'card_last_four.regex' => 'Card last four digits must contain only numbers.',
            'card_type.in' => 'Invalid card type selected.',
            'card_expiry_month.min' => 'Invalid expiry month.',
            'card_expiry_month.max' => 'Invalid expiry month.',
            'card_expiry_year.min' => 'Card expiry year cannot be in the past.',
            'card_expiry_year.max' => 'Card expiry year is too far in the future.',
            'payment_date.required' => 'Payment date is required.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
            'payer_email.email' => 'Payer email must be a valid email address.',
            'payer_phone.regex' => 'Payer phone number format is invalid.',
        ]);
    }

    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'billing_item_id' => 'billing item',
            'patient_id' => 'patient',
            'payment_method' => 'payment method',
            'payment_type' => 'payment type',
            'payment_status' => 'payment status',
            'transaction_id' => 'transaction ID',
            'reference_number' => 'reference number',
            'gateway_reference' => 'gateway reference',
            'authorization_code' => 'authorization code',
            'receipt_number' => 'receipt number',
            'gateway_name' => 'payment gateway',
            'gateway_response' => 'gateway response',
            'gateway_fee' => 'gateway fee',
            'gateway_status' => 'gateway status',
            'card_last_four' => 'card last four digits',
            'card_type' => 'card type',
            'card_expiry_month' => 'card expiry month',
            'card_expiry_year' => 'card expiry year',
            'bank_name' => 'bank name',
            'account_number' => 'account number',
            'routing_number' => 'routing number',
            'check_number' => 'check number',
            'payment_date' => 'payment date',
            'due_date' => 'due date',
            'processed_at' => 'processed date',
            'is_recurring' => 'recurring payment',
            'is_refundable' => 'refundable',
            'is_verified' => 'verified',
            'requires_approval' => 'requires approval',
            'processing_fee' => 'processing fee',
            'tax_amount' => 'tax amount',
            'discount_amount' => 'discount amount',
            'net_amount' => 'net amount',
            'payer_name' => 'payer name',
            'payer_email' => 'payer email',
            'payer_phone' => 'payer phone',
            'payer_address' => 'payer address',
            'internal_notes' => 'internal notes',
        ]);
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Auto-generate transaction ID if not provided
        if (!$this->has('transaction_id') || empty($this->input('transaction_id'))) {
            $this->merge(['transaction_id' => $this->generateTransactionId()]);
        }

        // Auto-generate receipt number if not provided
        if (!$this->has('receipt_number') || empty($this->input('receipt_number'))) {
            $this->merge(['receipt_number' => $this->generateReceiptNumber()]);
        }

        // Set default payment date if not provided
        if (!$this->has('payment_date') || empty($this->input('payment_date'))) {
            $this->merge(['payment_date' => now()->format('Y-m-d')]);
        }

        // Set default currency if not provided
        if (!$this->has('currency') || empty($this->input('currency'))) {
            $this->merge(['currency' => 'USD']); // Default to USD
        }

        // Calculate net amount if not provided
        if ($this->has('amount') && (!$this->has('net_amount') || empty($this->input('net_amount')))) {
            $netAmount = $this->input('amount') 
                - ($this->input('processing_fee') ?? 0) 
                - ($this->input('gateway_fee') ?? 0)
                - ($this->input('discount_amount') ?? 0)
                + ($this->input('tax_amount') ?? 0);
            $this->merge(['net_amount' => round($netAmount, 2)]);
        }

        // Set default flags
        if (!$this->has('is_recurring')) {
            $this->merge(['is_recurring' => false]);
        }

        if (!$this->has('is_refundable')) {
            $this->merge(['is_refundable' => true]);
        }

        if (!$this->has('is_verified')) {
            $this->merge(['is_verified' => false]);
        }

        if (!$this->has('requires_approval')) {
            $this->merge(['requires_approval' => $this->input('amount') > 10000]);
        }

        // Normalize payer information
        if ($this->has('payer_email')) {
            $this->merge(['payer_email' => strtolower(trim($this->input('payer_email')))]);
        }

        if ($this->has('payer_name')) {
            $this->merge(['payer_name' => ucwords(strtolower(trim($this->input('payer_name'))))]);
        }
    }

    /**
     * Generate unique transaction ID
     */
    private function generateTransactionId(): string
    {
        $prefix = 'TXN';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber(): string
    {
        $prefix = 'RCP';
        $date = now()->format('Ymd');
        $lastNumber = \App\Models\PaymentRecord::where('receipt_number', 'like', $prefix . $date . '%')
            ->count() + 1;

        return $prefix . $date . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Additional validation logic for complex business rules
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate payment amount against billing item
            if ($this->filled(['billing_item_id', 'amount'])) {
                $billingItem = \App\Models\BillingItem::find($this->input('billing_item_id'));
                if ($billingItem) {
                    $remainingAmount = $billingItem->final_amount - $billingItem->paid_amount;
                    
                    if ($this->input('amount') > $remainingAmount) {
                        $validator->errors()->add('amount', 
                            'Payment amount cannot exceed the remaining balance of ' . 
                            number_format($remainingAmount, 2));
                    }
                }
            }

            // Validate card expiry date
            if ($this->filled(['card_expiry_month', 'card_expiry_year'])) {
                $currentMonth = (int) date('m');
                $currentYear = (int) date('Y');
                $expiryMonth = (int) $this->input('card_expiry_month');
                $expiryYear = (int) $this->input('card_expiry_year');
                
                if ($expiryYear == $currentYear && $expiryMonth < $currentMonth) {
                    $validator->errors()->add('card_expiry_month', 
                        'Card has already expired.');
                }
            }

            // Validate net amount calculation
            if ($this->filled(['amount', 'net_amount'])) {
                $calculatedNet = $this->input('amount') 
                    - ($this->input('processing_fee') ?? 0) 
                    - ($this->input('gateway_fee') ?? 0)
                    - ($this->input('discount_amount') ?? 0)
                    + ($this->input('tax_amount') ?? 0);
                $providedNet = (float) $this->input('net_amount');
                
                if (abs($calculatedNet - $providedNet) > 0.01) {
                    $validator->errors()->add('net_amount', 
                        'Net amount calculation is incorrect.');
                }
            }

            // Validate payment method specific fields
            if ($this->input('payment_method') === 'Credit Card' || 
                $this->input('payment_method') === 'Debit Card') {
                
                if (!$this->filled('card_last_four')) {
                    $validator->errors()->add('card_last_four', 
                        'Card last four digits are required for card payments.');
                }
                
                if (!$this->filled('card_type')) {
                    $validator->errors()->add('card_type', 
                        'Card type is required for card payments.');
                }
            }

            if ($this->input('payment_method') === 'Bank Transfer') {
                if (!$this->filled('bank_name')) {
                    $validator->errors()->add('bank_name', 
                        'Bank name is required for bank transfers.');
                }
            }

            if ($this->input('payment_method') === 'Check') {
                if (!$this->filled('check_number')) {
                    $validator->errors()->add('check_number', 
                        'Check number is required for check payments.');
                }
            }

            // Validate patient belongs to the same billing item
            if ($this->filled(['billing_item_id', 'patient_id'])) {
                $billingItem = \App\Models\BillingItem::find($this->input('billing_item_id'));
                if ($billingItem && $billingItem->patient_id != $this->input('patient_id')) {
                    $validator->errors()->add('patient_id', 
                        'Patient must match the billing item patient.');
                }
            }

            // Validate refund amount
            if ($this->input('payment_type') === 'Refund') {
                if ($this->input('amount') > 0) {
                    $validator->errors()->add('amount', 
                        'Refund amount should be negative or use negative value.');
                }
            }

            // Validate gateway requirements for online payments
            if (in_array($this->input('payment_method'), ['Credit Card', 'Debit Card', 'Online Payment', 'Mobile Payment'])) {
                if (!$this->filled('gateway_name')) {
                    $validator->errors()->add('gateway_name', 
                        'Payment gateway is required for online payments.');
                }
            }
        });
    }
}
