<?php

namespace App\Admin\Traits;

use Encore\Admin\Form;
use Illuminate\Validation\Rule;

trait FormValidationTrait
{
    /**
     * Common validation rules for different field types
     */
    protected function getValidationRules()
    {
        return [
            'required_string' => 'required|string|max:255',
            'optional_string' => 'nullable|string|max:255',
            'required_text' => 'required|string',
            'optional_text' => 'nullable|string',
            'required_email' => 'required|email|max:255',
            'optional_email' => 'nullable|email|max:255',
            'required_phone' => 'required|string|regex:/^[\+]?[0-9\s\-\(\)]{7,15}$/',
            'optional_phone' => 'nullable|string|regex:/^[\+]?[0-9\s\-\(\)]{7,15}$/',
            'required_date' => 'required|date',
            'optional_date' => 'nullable|date',
            'required_datetime' => 'required|date_format:Y-m-d H:i:s',
            'optional_datetime' => 'nullable|date_format:Y-m-d H:i:s',
            'required_numeric' => 'required|numeric|min:0',
            'optional_numeric' => 'nullable|numeric|min:0',
            'required_integer' => 'required|integer|min:0',
            'optional_integer' => 'nullable|integer|min:0',
            'required_decimal' => 'required|numeric|between:0,999999.99',
            'optional_decimal' => 'nullable|numeric|between:0,999999.99',
            'required_file' => 'required|file|max:10240', // 10MB max
            'optional_file' => 'nullable|file|max:10240',
            'required_image' => 'required|image|max:5120', // 5MB max
            'optional_image' => 'nullable|image|max:5120',
        ];
    }

    /**
     * Apply validation rule to form field
     */
    protected function applyValidation($field, $ruleType)
    {
        $rules = $this->getValidationRules();
        
        if (isset($rules[$ruleType])) {
            return $field->rules($rules[$ruleType]);
        }
        
        return $field;
    }

    /**
     * Add standard contact information fields
     */
    protected function addContactFields(Form $form, $required = true)
    {
        $emailRule = $required ? 'required_email' : 'optional_email';
        $phoneRule = $required ? 'required_phone' : 'optional_phone';
        
        $form->email('email', 'Email Address')
             ->rules($this->getValidationRules()[$emailRule]);
             
        $form->text('phone', 'Phone Number')
             ->rules($this->getValidationRules()[$phoneRule]);
             
        return $form;
    }

    /**
     * Add standard address fields
     */
    protected function addAddressFields(Form $form, $required = false)
    {
        $rule = $required ? 'required_string' : 'optional_string';
        
        $form->text('address', 'Address')
             ->rules($this->getValidationRules()[$rule]);
             
        $form->text('city', 'City')
             ->rules($this->getValidationRules()[$rule]);
             
        $form->text('state', 'State/Province')
             ->rules($this->getValidationRules()[$rule]);
             
        $form->text('postal_code', 'Postal Code')
             ->rules($this->getValidationRules()[$rule]);
             
        return $form;
    }

    /**
     * Add standard financial fields
     */
    protected function addFinancialFields(Form $form, $fields = ['amount'])
    {
        foreach ($fields as $field) {
            $label = ucfirst(str_replace('_', ' ', $field));
            
            $form->decimal($field, $label)
                 ->rules($this->getValidationRules()['required_decimal']);
        }
        
        return $form;
    }

    /**
     * Add standard date range fields
     */
    protected function addDateRangeFields(Form $form, $startField = 'start_date', $endField = 'end_date')
    {
        $form->date($startField, 'Start Date')
             ->rules($this->getValidationRules()['required_date']);
             
        $form->date($endField, 'End Date')
             ->rules($this->getValidationRules()['required_date'] . '|after_or_equal:' . $startField);
             
        return $form;
    }

    /**
     * Add standard status field
     */
    protected function addStatusField(Form $form, $options = null, $field = 'status')
    {
        $defaultOptions = [
            'pending' => 'Pending',
            'active' => 'Active', 
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        
        $options = $options ?: $defaultOptions;
        
        $form->select($field, ucfirst($field))
             ->options($options)
             ->rules('required|in:' . implode(',', array_keys($options)))
             ->default('pending');
             
        return $form;
    }

    /**
     * Custom validation rule for unique within enterprise
     */
    protected function uniqueInEnterprise($table, $column, $enterpriseId, $ignoreId = null)
    {
        $rule = Rule::unique($table, $column)->where('enterprise_id', $enterpriseId);
        
        if ($ignoreId) {
            $rule->ignore($ignoreId);
        }
        
        return $rule;
    }

    /**
     * Add file upload field with validation
     */
    protected function addFileField(Form $form, $field, $label, $required = false, $accept = null)
    {
        $fileField = $form->file($field, $label);
        
        $rule = $required ? 'required_file' : 'optional_file';
        $fileField->rules($this->getValidationRules()[$rule]);
        
        if ($accept) {
            $fileField->accept($accept);
        }
        
        return $form;
    }

    /**
     * Add image upload field with validation
     */
    protected function addImageField(Form $form, $field, $label, $required = false)
    {
        $imageField = $form->image($field, $label);
        
        $rule = $required ? 'required_image' : 'optional_image';
        $imageField->rules($this->getValidationRules()[$rule]);
        
        return $form;
    }
}
