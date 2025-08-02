<?php

namespace App\Http\Requests;

/**
 * PatientRequest - Handles patient registration and update form validation
 * 
 * Validates patient information with medical requirements,
 * enterprise scoping, and data privacy considerations
 */
class PatientRequest extends BaseRequest
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
        $patientId = $this->route('patient') ?? $this->route('user');
        
        return array_merge([
            // Basic Information
            'first_name' => 'required|string|max:50|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'last_name' => 'required|string|max:50|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $patientId
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
                'unique:users,phone,' . $patientId
            ],
            'alternate_phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            
            // Personal Details
            'date_of_birth' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:' . now()->subYears(150)->format('Y-m-d')
            ],
            'gender' => 'required|in:Male,Female,Other',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed,Separated',
            'nationality' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:100',

            // Address Information
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'landmark' => 'nullable|string|max:200',

            // Emergency Contact
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'emergency_contact_relationship' => 'required|string|max:50',
            'emergency_contact_address' => 'nullable|string|max:500',

            // Medical Information
            'allergies' => 'nullable|string|max:1000',
            'medical_history' => 'nullable|string|max:2000',
            'current_medications' => 'nullable|string|max:1000',
            'previous_surgeries' => 'nullable|string|max:1000',
            'family_medical_history' => 'nullable|string|max:1000',
            'smoking_status' => 'nullable|in:Never,Former,Current',
            'alcohol_consumption' => 'nullable|in:Never,Occasional,Regular,Heavy',
            'exercise_frequency' => 'nullable|in:Never,Rarely,Sometimes,Regularly,Daily',

            // Insurance Information
            'insurance_provider' => 'nullable|string|max:100',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_group_number' => 'nullable|string|max:50',
            'insurance_expiry_date' => 'nullable|date|after:today',

            // Identification
            'id_type' => 'nullable|in:National ID,Passport,Driver License,Voter ID,Other',
            'id_number' => [
                'nullable',
                'string',
                'max:50',
                'unique:users,id_number,' . $patientId
            ],

            // Medical Record Settings
            'preferred_language' => 'nullable|string|max:50',
            'communication_preference' => 'nullable|in:Phone,Email,SMS,Postal',
            'privacy_consent' => 'required|boolean',
            'marketing_consent' => 'nullable|boolean',

            // System Fields
            'status' => 'nullable|in:Active,Inactive,Suspended',
            'notes' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:1000',
        ], $this->medicalRules(), $this->dateRules());
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.required' => 'Last name is required.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please provide a valid phone number.',
            'phone.unique' => 'This phone number is already registered.',
            'alternate_phone.regex' => 'Please provide a valid alternate phone number.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
            'date_of_birth.after' => 'Date of birth indicates an unrealistic age.',
            'gender.required' => 'Gender selection is required.',
            'gender.in' => 'Please select a valid gender option.',
            'blood_group.in' => 'Please select a valid blood group.',
            'marital_status.in' => 'Please select a valid marital status.',
            'address.required' => 'Address is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State/Province is required.',
            'country.required' => 'Country is required.',
            'emergency_contact_name.required' => 'Emergency contact name is required.',
            'emergency_contact_phone.required' => 'Emergency contact phone is required.',
            'emergency_contact_phone.regex' => 'Please provide a valid emergency contact phone number.',
            'emergency_contact_relationship.required' => 'Emergency contact relationship is required.',
            'smoking_status.in' => 'Please select a valid smoking status.',
            'alcohol_consumption.in' => 'Please select a valid alcohol consumption level.',
            'exercise_frequency.in' => 'Please select a valid exercise frequency.',
            'insurance_expiry_date.after' => 'Insurance expiry date must be in the future.',
            'id_type.in' => 'Please select a valid ID type.',
            'id_number.unique' => 'This ID number is already registered.',
            'communication_preference.in' => 'Please select a valid communication preference.',
            'privacy_consent.required' => 'Privacy consent is required to proceed.',
            'status.in' => 'Please select a valid status.',
        ]);
    }

    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'date_of_birth' => 'date of birth',
            'blood_group' => 'blood group',
            'marital_status' => 'marital status',
            'postal_code' => 'postal code',
            'emergency_contact_name' => 'emergency contact name',
            'emergency_contact_phone' => 'emergency contact phone',
            'emergency_contact_relationship' => 'emergency contact relationship',
            'emergency_contact_address' => 'emergency contact address',
            'medical_history' => 'medical history',
            'current_medications' => 'current medications',
            'previous_surgeries' => 'previous surgeries',
            'family_medical_history' => 'family medical history',
            'smoking_status' => 'smoking status',
            'alcohol_consumption' => 'alcohol consumption',
            'exercise_frequency' => 'exercise frequency',
            'insurance_provider' => 'insurance provider',
            'insurance_policy_number' => 'insurance policy number',
            'insurance_group_number' => 'insurance group number',
            'insurance_expiry_date' => 'insurance expiry date',
            'id_type' => 'ID type',
            'id_number' => 'ID number',
            'preferred_language' => 'preferred language',
            'communication_preference' => 'communication preference',
            'privacy_consent' => 'privacy consent',
            'marketing_consent' => 'marketing consent',
            'internal_notes' => 'internal notes',
        ]);
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Normalize names
        if ($this->has('first_name')) {
            $this->merge([
                'first_name' => ucwords(strtolower(trim($this->input('first_name'))))
            ]);
        }
        
        if ($this->has('last_name')) {
            $this->merge([
                'last_name' => ucwords(strtolower(trim($this->input('last_name'))))
            ]);
        }

        // Normalize email
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->input('email')))
            ]);
        }

        // Normalize phone numbers
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/\s+/', ' ', trim($this->input('phone')))
            ]);
        }

        if ($this->has('alternate_phone')) {
            $this->merge([
                'alternate_phone' => preg_replace('/\s+/', ' ', trim($this->input('alternate_phone')))
            ]);
        }

        if ($this->has('emergency_contact_phone')) {
            $this->merge([
                'emergency_contact_phone' => preg_replace('/\s+/', ' ', trim($this->input('emergency_contact_phone')))
            ]);
        }

        // Normalize address fields
        $addressFields = ['address', 'city', 'state', 'country', 'landmark', 'emergency_contact_address'];
        foreach ($addressFields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => ucwords(strtolower(trim($this->input($field))))
                ]);
            }
        }

        // Normalize postal code
        if ($this->has('postal_code')) {
            $this->merge([
                'postal_code' => strtoupper(trim($this->input('postal_code')))
            ]);
        }

        // Normalize ID number
        if ($this->has('id_number')) {
            $this->merge([
                'id_number' => strtoupper(trim($this->input('id_number')))
            ]);
        }

        // Set default values
        if (!$this->has('status') || empty($this->input('status'))) {
            $this->merge(['status' => 'Active']);
        }

        if (!$this->has('privacy_consent')) {
            $this->merge(['privacy_consent' => false]);
        }

        if (!$this->has('marketing_consent')) {
            $this->merge(['marketing_consent' => false]);
        }

        // Calculate age from date of birth
        if ($this->has('date_of_birth')) {
            $birthDate = \Carbon\Carbon::parse($this->input('date_of_birth'));
            $age = $birthDate->age;
            $this->merge(['age' => $age]);
        }
    }

    /**
     * Additional validation logic for complex business rules
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate age restrictions
            if ($this->filled('date_of_birth')) {
                $age = \Carbon\Carbon::parse($this->input('date_of_birth'))->age;
                
                if ($age < 0) {
                    $validator->errors()->add('date_of_birth', 
                        'Date of birth cannot be in the future.');
                }
                
                if ($age > 150) {
                    $validator->errors()->add('date_of_birth', 
                        'Date of birth indicates an unrealistic age.');
                }
            }

            // Validate phone numbers are different
            if ($this->filled(['phone', 'alternate_phone'])) {
                if ($this->input('phone') === $this->input('alternate_phone')) {
                    $validator->errors()->add('alternate_phone', 
                        'Alternate phone number must be different from primary phone.');
                }
            }

            if ($this->filled(['phone', 'emergency_contact_phone'])) {
                if ($this->input('phone') === $this->input('emergency_contact_phone')) {
                    $validator->errors()->add('emergency_contact_phone', 
                        'Emergency contact phone must be different from patient phone.');
                }
            }

            // Validate insurance information completeness
            if ($this->filled('insurance_provider') || 
                $this->filled('insurance_policy_number') || 
                $this->filled('insurance_group_number')) {
                
                if (!$this->filled('insurance_provider')) {
                    $validator->errors()->add('insurance_provider', 
                        'Insurance provider is required when insurance information is provided.');
                }
                
                if (!$this->filled('insurance_policy_number')) {
                    $validator->errors()->add('insurance_policy_number', 
                        'Insurance policy number is required when insurance information is provided.');
                }
            }

            // Validate ID information completeness
            if ($this->filled('id_number') && !$this->filled('id_type')) {
                $validator->errors()->add('id_type', 
                    'ID type is required when ID number is provided.');
            }

            if ($this->filled('id_type') && !$this->filled('id_number')) {
                $validator->errors()->add('id_number', 
                    'ID number is required when ID type is provided.');
            }

            // Validate privacy consent for minors
            if ($this->filled('date_of_birth')) {
                $age = \Carbon\Carbon::parse($this->input('date_of_birth'))->age;
                
                if ($age < 18 && !$this->filled('emergency_contact_name')) {
                    $validator->errors()->add('emergency_contact_name', 
                        'Emergency contact is mandatory for patients under 18 years old.');
                }
            }

            // Validate email domain for corporate accounts
            if ($this->filled('email')) {
                $email = $this->input('email');
                $domain = substr(strrchr($email, "@"), 1);
                
                // Add any domain restrictions if needed
                $restrictedDomains = ['tempmail.org', '10minutemail.com'];
                if (in_array($domain, $restrictedDomains)) {
                    $validator->errors()->add('email', 
                        'Temporary email addresses are not allowed.');
                }
            }
        });
    }
}
