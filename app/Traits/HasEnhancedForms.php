<?php

namespace App\Traits;

trait HasEnhancedForms
{
    /**
     * Get form field configuration for a model
     */
    public function getFormFieldsConfig()
    {
        $modelName = class_basename($this);
        
        // Define field configurations for different models
        $configurations = [
            'Patient' => [
                'basic_info' => [
                    'title' => 'Basic Information',
                    'fields' => [
                        'first_name' => [
                            'type' => 'text',
                            'label' => 'First Name',
                            'required' => true,
                            'placeholder' => 'Enter first name',
                            'icon' => 'fas fa-user'
                        ],
                        'last_name' => [
                            'type' => 'text',
                            'label' => 'Last Name',
                            'required' => true,
                            'placeholder' => 'Enter last name',
                            'icon' => 'fas fa-user'
                        ],
                        'email' => [
                            'type' => 'email',
                            'label' => 'Email Address',
                            'required' => true,
                            'placeholder' => 'Enter email address',
                            'icon' => 'fas fa-envelope'
                        ],
                        'phone' => [
                            'type' => 'text',
                            'label' => 'Phone Number',
                            'required' => true,
                            'placeholder' => 'Enter phone number',
                            'icon' => 'fas fa-phone'
                        ],
                        'gender' => [
                            'type' => 'select',
                            'label' => 'Gender',
                            'required' => true,
                            'options' => [
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other'
                            ],
                            'icon' => 'fas fa-venus-mars'
                        ],
                        'date_of_birth' => [
                            'type' => 'date',
                            'label' => 'Date of Birth',
                            'required' => true,
                            'icon' => 'fas fa-calendar'
                        ]
                    ]
                ],
                'contact_info' => [
                    'title' => 'Contact Information',
                    'fields' => [
                        'address' => [
                            'type' => 'textarea',
                            'label' => 'Address',
                            'required' => true,
                            'placeholder' => 'Enter full address',
                            'icon' => 'fas fa-map-marker-alt',
                            'rows' => 3
                        ],
                        'emergency_contact_name' => [
                            'type' => 'text',
                            'label' => 'Emergency Contact Name',
                            'required' => true,
                            'placeholder' => 'Enter emergency contact name',
                            'icon' => 'fas fa-user-friends'
                        ],
                        'emergency_contact_phone' => [
                            'type' => 'text',
                            'label' => 'Emergency Contact Phone',
                            'required' => true,
                            'placeholder' => 'Enter emergency contact phone',
                            'icon' => 'fas fa-phone'
                        ],
                        'insurance_number' => [
                            'type' => 'text',
                            'label' => 'Insurance Number',
                            'required' => false,
                            'placeholder' => 'Enter insurance number',
                            'icon' => 'fas fa-id-card'
                        ]
                    ]
                ]
            ],
            'Doctor' => [
                'personal_info' => [
                    'title' => 'Personal Information',
                    'fields' => [
                        'first_name' => [
                            'type' => 'text',
                            'label' => 'First Name',
                            'required' => true,
                            'placeholder' => 'Enter first name',
                            'icon' => 'fas fa-user-md'
                        ],
                        'last_name' => [
                            'type' => 'text',
                            'label' => 'Last Name',
                            'required' => true,
                            'placeholder' => 'Enter last name',
                            'icon' => 'fas fa-user-md'
                        ],
                        'email' => [
                            'type' => 'email',
                            'label' => 'Email Address',
                            'required' => true,
                            'placeholder' => 'Enter email address',
                            'icon' => 'fas fa-envelope'
                        ],
                        'phone' => [
                            'type' => 'text',
                            'label' => 'Phone Number',
                            'required' => true,
                            'placeholder' => 'Enter phone number',
                            'icon' => 'fas fa-phone'
                        ],
                        'specialization' => [
                            'type' => 'select',
                            'label' => 'Specialization',
                            'required' => true,
                            'options' => [
                                'cardiology' => 'Cardiology',
                                'neurology' => 'Neurology',
                                'orthopedics' => 'Orthopedics',
                                'pediatrics' => 'Pediatrics',
                                'general_medicine' => 'General Medicine',
                                'surgery' => 'Surgery',
                                'dermatology' => 'Dermatology',
                                'psychiatry' => 'Psychiatry'
                            ],
                            'icon' => 'fas fa-stethoscope'
                        ]
                    ]
                ],
                'professional_info' => [
                    'title' => 'Professional Information',
                    'fields' => [
                        'license_number' => [
                            'type' => 'text',
                            'label' => 'Medical License Number',
                            'required' => true,
                            'placeholder' => 'Enter license number',
                            'icon' => 'fas fa-certificate'
                        ],
                        'experience_years' => [
                            'type' => 'number',
                            'label' => 'Years of Experience',
                            'required' => true,
                            'placeholder' => 'Enter years of experience',
                            'icon' => 'fas fa-clock'
                        ],
                        'consultation_fee' => [
                            'type' => 'number',
                            'label' => 'Consultation Fee',
                            'required' => true,
                            'placeholder' => 'Enter consultation fee',
                            'icon' => 'fas fa-dollar-sign',
                            'step' => '0.01'
                        ],
                        'status' => [
                            'type' => 'select',
                            'label' => 'Status',
                            'required' => true,
                            'options' => [
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'on_leave' => 'On Leave'
                            ],
                            'icon' => 'fas fa-toggle-on'
                        ]
                    ]
                ]
            ],
            'Appointment' => [
                'appointment_details' => [
                    'title' => 'Appointment Details',
                    'fields' => [
                        'patient_id' => [
                            'type' => 'select',
                            'label' => 'Patient',
                            'required' => true,
                            'options' => [], // Will be populated dynamically
                            'icon' => 'fas fa-user'
                        ],
                        'doctor_id' => [
                            'type' => 'select',
                            'label' => 'Doctor',
                            'required' => true,
                            'options' => [], // Will be populated dynamically
                            'icon' => 'fas fa-user-md'
                        ],
                        'appointment_date' => [
                            'type' => 'date',
                            'label' => 'Appointment Date',
                            'required' => true,
                            'icon' => 'fas fa-calendar'
                        ],
                        'appointment_time' => [
                            'type' => 'time',
                            'label' => 'Appointment Time',
                            'required' => true,
                            'icon' => 'fas fa-clock'
                        ],
                        'purpose' => [
                            'type' => 'textarea',
                            'label' => 'Purpose of Visit',
                            'required' => true,
                            'placeholder' => 'Describe the purpose of the appointment',
                            'icon' => 'fas fa-notes-medical',
                            'rows' => 3
                        ],
                        'status' => [
                            'type' => 'select',
                            'label' => 'Status',
                            'required' => true,
                            'options' => [
                                'scheduled' => 'Scheduled',
                                'confirmed' => 'Confirmed',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'no_show' => 'No Show'
                            ],
                            'icon' => 'fas fa-clipboard-check'
                        ]
                    ]
                ]
            ],
            'MedicalRecord' => [
                'medical_details' => [
                    'title' => 'Medical Record Details',
                    'fields' => [
                        'patient_id' => [
                            'type' => 'select',
                            'label' => 'Patient',
                            'required' => true,
                            'options' => [], // Will be populated dynamically
                            'icon' => 'fas fa-user'
                        ],
                        'doctor_id' => [
                            'type' => 'select',
                            'label' => 'Doctor',
                            'required' => true,
                            'options' => [], // Will be populated dynamically
                            'icon' => 'fas fa-user-md'
                        ],
                        'diagnosis' => [
                            'type' => 'textarea',
                            'label' => 'Diagnosis',
                            'required' => true,
                            'placeholder' => 'Enter diagnosis details',
                            'icon' => 'fas fa-stethoscope',
                            'rows' => 4
                        ],
                        'treatment' => [
                            'type' => 'textarea',
                            'label' => 'Treatment',
                            'required' => true,
                            'placeholder' => 'Enter treatment details',
                            'icon' => 'fas fa-pills',
                            'rows' => 4
                        ],
                        'prescription' => [
                            'type' => 'textarea',
                            'label' => 'Prescription',
                            'required' => false,
                            'placeholder' => 'Enter prescription details',
                            'icon' => 'fas fa-prescription',
                            'rows' => 3
                        ],
                        'notes' => [
                            'type' => 'textarea',
                            'label' => 'Additional Notes',
                            'required' => false,
                            'placeholder' => 'Enter any additional notes',
                            'icon' => 'fas fa-sticky-note',
                            'rows' => 3
                        ]
                    ]
                ]
            ]
        ];

        return $configurations[$modelName] ?? [];
    }

    /**
     * Get validation rules for form fields
     */
    public function getFormValidationRules()
    {
        $fields = $this->getFormFieldsConfig();
        $rules = [];

        foreach ($fields as $section) {
            foreach ($section['fields'] as $fieldName => $fieldConfig) {
                $fieldRules = [];

                if ($fieldConfig['required']) {
                    $fieldRules[] = 'required';
                }

                switch ($fieldConfig['type']) {
                    case 'email':
                        $fieldRules[] = 'email';
                        break;
                    case 'number':
                        $fieldRules[] = 'numeric';
                        if (isset($fieldConfig['min'])) {
                            $fieldRules[] = 'min:' . $fieldConfig['min'];
                        }
                        if (isset($fieldConfig['max'])) {
                            $fieldRules[] = 'max:' . $fieldConfig['max'];
                        }
                        break;
                    case 'date':
                        $fieldRules[] = 'date';
                        break;
                    case 'time':
                        $fieldRules[] = 'date_format:H:i';
                        break;
                    case 'select':
                        if (isset($fieldConfig['options'])) {
                            $fieldRules[] = 'in:' . implode(',', array_keys($fieldConfig['options']));
                        }
                        break;
                }

                if (!empty($fieldRules)) {
                    $rules[$fieldName] = implode('|', $fieldRules);
                }
            }
        }

        return $rules;
    }

    /**
     * Get form field labels for validation messages
     */
    public function getFormFieldLabels()
    {
        $fields = $this->getFormFieldsConfig();
        $labels = [];

        foreach ($fields as $section) {
            foreach ($section['fields'] as $fieldName => $fieldConfig) {
                $labels[$fieldName] = $fieldConfig['label'];
            }
        }

        return $labels;
    }

    /**
     * Format form data for display
     */
    public function formatFormData($data)
    {
        $fields = $this->getFormFieldsConfig();
        $formatted = [];

        foreach ($fields as $sectionName => $section) {
            $formatted[$sectionName] = [
                'title' => $section['title'],
                'fields' => []
            ];

            foreach ($section['fields'] as $fieldName => $fieldConfig) {
                $value = $data[$fieldName] ?? null;

                // Format value based on field type
                if ($value !== null) {
                    switch ($fieldConfig['type']) {
                        case 'select':
                            if (isset($fieldConfig['options'][$value])) {
                                $value = $fieldConfig['options'][$value];
                            }
                            break;
                        case 'date':
                            $value = \Carbon\Carbon::parse($value)->format('F j, Y');
                            break;
                        case 'time':
                            $value = \Carbon\Carbon::parse($value)->format('g:i A');
                            break;
                    }
                }

                $formatted[$sectionName]['fields'][$fieldName] = [
                    'label' => $fieldConfig['label'],
                    'value' => $value,
                    'icon' => $fieldConfig['icon'] ?? null
                ];
            }
        }

        return $formatted;
    }
}
