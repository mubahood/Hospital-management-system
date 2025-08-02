<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * BaseRequest - Foundation for all form request validation
 * 
 * Provides common validation functionality, error handling,
 * and enterprise-aware authorization for all requests
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * Common authorization logic for all requests
     */
    public function authorize(): bool
    {
        // All requests require authentication
        if (!auth()->check()) {
            return false;
        }

        // Enterprise-specific authorization if required
        if (method_exists($this, 'authorizeEnterprise')) {
            return $this->authorizeEnterprise();
        }

        return true;
    }

    /**
     * Common validation rules that apply to all requests
     */
    public function baseRules(): array
    {
        return [
            'enterprise_id' => 'sometimes|exists:enterprises,id',
        ];
    }

    /**
     * Get the validation rules that apply to the request
     */
    abstract public function rules(): array;

    /**
     * Merge base rules with specific request rules
     */
    public function getAllRules(): array
    {
        return array_merge($this->baseRules(), $this->rules());
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'enterprise_id.exists' => 'The selected enterprise is invalid.',
            'required' => 'The :attribute field is required.',
            'email' => 'The :attribute must be a valid email address.',
            'unique' => 'The :attribute has already been taken.',
            'min' => 'The :attribute must be at least :min characters.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'numeric' => 'The :attribute must be a number.',
            'date' => 'The :attribute is not a valid date.',
            'in' => 'The selected :attribute is invalid.',
        ];
    }

    /**
     * Custom attribute names for error messages
     */
    public function attributes(): array
    {
        return [
            'enterprise_id' => 'enterprise',
            'created_at' => 'creation date',
            'updated_at' => 'last updated',
        ];
    }

    /**
     * Handle a failed validation attempt
     */
    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'status_code' => 422
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // Auto-assign enterprise_id if not provided and user is authenticated
        if (auth()->check() && !$this->has('enterprise_id') && auth()->user()->enterprise_id) {
            $this->merge([
                'enterprise_id' => auth()->user()->enterprise_id
            ]);
        }

        // Sanitize input data
        $this->sanitizeInput();
    }

    /**
     * Sanitize input data to prevent XSS and other attacks
     */
    protected function sanitizeInput(): void
    {
        $sanitized = [];
        
        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                // Remove potentially dangerous HTML tags but keep basic formatting
                $sanitized[$key] = strip_tags($value, '<p><br><strong><em><ul><ol><li>');
                // Trim whitespace
                $sanitized[$key] = trim($sanitized[$key]);
            } else {
                $sanitized[$key] = $value;
            }
        }

        $this->replace($sanitized);
    }

    /**
     * Check if user belongs to the same enterprise as the resource
     */
    protected function authorizeEnterprise(): bool
    {
        if (!auth()->user()->enterprise_id) {
            return false; // User must belong to an enterprise
        }

        // If request contains enterprise_id, verify it matches user's enterprise
        if ($this->has('enterprise_id')) {
            return $this->input('enterprise_id') == auth()->user()->enterprise_id;
        }

        return true;
    }

    /**
     * Get validated data with enterprise_id automatically included
     */
    public function validatedWithEnterprise(): array
    {
        $validated = $this->validated();
        
        if (auth()->check() && auth()->user()->enterprise_id && !isset($validated['enterprise_id'])) {
            $validated['enterprise_id'] = auth()->user()->enterprise_id;
        }

        return $validated;
    }

    /**
     * Common medical field validation rules
     */
    protected function medicalRules(): array
    {
        return [
            'blood_pressure_systolic' => 'nullable|numeric|min:50|max:300',
            'blood_pressure_diastolic' => 'nullable|numeric|min:30|max:200',
            'heart_rate' => 'nullable|numeric|min:30|max:220',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'weight' => 'nullable|numeric|min:0.5|max:500',
            'height' => 'nullable|numeric|min:30|max:250',
            'bmi' => 'nullable|numeric|min:10|max:100',
        ];
    }

    /**
     * Common financial field validation rules
     */
    protected function financialRules(): array
    {
        return [
            'amount' => 'required|numeric|min:0|max:9999999.99',
            'price' => 'required|numeric|min:0|max:9999999.99',
            'total' => 'nullable|numeric|min:0|max:9999999.99',
            'balance' => 'nullable|numeric|min:0|max:9999999.99',
            'discount' => 'nullable|numeric|min:0|max:100',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Common date field validation rules
     */
    protected function dateRules(): array
    {
        return [
            'consultation_date' => 'nullable|date|after_or_equal:today',
            'birth_date' => 'nullable|date|before:today',
            'due_date' => 'nullable|date|after_or_equal:today',
            'appointment_date' => 'nullable|date|after_or_equal:today',
        ];
    }
}
