<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'enterprise_id' => 1,
            'user_type' => $this->faker->randomElement(['patient', 'doctor', 'nurse', 'admin']),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'phone_number_1' => $this->faker->phoneNumber(),
            'phone_number_2' => $this->faker->optional()->phoneNumber(),
            'sex' => $this->faker->randomElement(['Male', 'Female']),
            'date_of_birth' => $this->faker->date('Y-m-d', '-18 years'),
            'current_address' => $this->faker->address(),
            'emergency_person_name' => $this->faker->name(),
            'emergency_person_phone' => $this->faker->phoneNumber(),
            'belongs_to_company' => $this->faker->randomElement(['Yes', 'No']),
            'is_dependent' => $this->faker->randomElement(['Yes', 'No']),
            'card_status' => $this->faker->randomElement(['Active', 'Inactive', 'Suspended']),
            'medical_history' => $this->faker->optional()->paragraph(),
            'allergies' => $this->faker->optional()->words(3, true),
            'current_medications' => $this->faker->optional()->words(3, true),
            'insurance_provider' => $this->faker->optional()->company(),
            'insurance_policy_number' => $this->faker->optional()->numerify('POL-#######'),
            'insurance_expiry_date' => $this->faker->optional()->date('Y-m-d', '+1 year'),
            'family_doctor_name' => $this->faker->optional()->name(),
            'family_doctor_phone' => $this->faker->optional()->phoneNumber(),
            'employment_status' => $this->faker->optional()->randomElement(['Employed', 'Unemployed', 'Student', 'Retired']),
            'employer_name' => $this->faker->optional()->company(),
            'annual_income' => $this->faker->optional()->numberBetween(1000000, 50000000),
            'education_level' => $this->faker->optional()->randomElement(['Primary', 'Secondary', 'Certificate', 'Diploma', 'Bachelor', 'Master', 'PhD']),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Patient state
     */
    public function patient()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => 'patient',
                'role' => 'Patient',
                'belongs_to_company' => $this->faker->randomElement(['Yes', 'No']),
                'is_dependent' => $this->faker->randomElement(['Yes', 'No']),
                'card_status' => $this->faker->randomElement(['Active', 'Inactive', 'Suspended']),
                'medical_history' => $this->faker->paragraph(),
                'allergies' => $this->faker->words(3, true),
                'current_medications' => $this->faker->optional()->words(3, true),
            ];
        });
    }

    /**
     * Doctor state
     */
    public function doctor()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => 'doctor',
                'role' => 'Doctor',
                'medical_license_number' => 'MD' . $this->faker->numberBetween(10000, 99999),
                'specialization' => $this->faker->randomElement([
                    'General Medicine', 'Cardiology', 'Neurology', 'Orthopedics', 
                    'Pediatrics', 'Gynecology', 'Surgery', 'Internal Medicine'
                ]),
                'years_of_experience' => $this->faker->numberBetween(2, 30),
            ];
        });
    }

    /**
     * Nurse state
     */
    public function nurse()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => 'nurse',
                'role' => 'Nurse',
                'medical_license_number' => 'RN' . $this->faker->numberBetween(10000, 99999),
                'specialization' => $this->faker->randomElement([
                    'General Nursing', 'ICU', 'Emergency', 'Pediatric', 'Surgical', 'Maternity'
                ]),
                'years_of_experience' => $this->faker->numberBetween(1, 25),
            ];
        });
    }

    /**
     * Receptionist state
     */
    public function receptionist()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => 'receptionist',
                'role' => 'Receptionist',
                'years_of_experience' => $this->faker->numberBetween(1, 15),
            ];
        });
    }

    /**
     * Specialist state
     */
    public function specialist()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => 'specialist',
                'role' => 'Specialist',
                'medical_license_number' => 'SP' . $this->faker->numberBetween(10000, 99999),
                'specialization' => $this->faker->randomElement([
                    'Cardiologist', 'Neurologist', 'Orthopedic Surgeon', 'Pediatrician',
                    'Gynecologist', 'Dermatologist', 'Psychiatrist', 'Radiologist'
                ]),
                'years_of_experience' => $this->faker->numberBetween(5, 35),
            ];
        });
    }

    /**
     * Surgeon state
     */
    public function surgeon()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => 'surgeon',
                'role' => 'Surgeon',
                'medical_license_number' => 'SG' . $this->faker->numberBetween(10000, 99999),
                'specialization' => $this->faker->randomElement([
                    'General Surgery', 'Cardiac Surgery', 'Neurosurgery', 'Orthopedic Surgery',
                    'Plastic Surgery', 'Urological Surgery'
                ]),
                'years_of_experience' => $this->faker->numberBetween(8, 40),
            ];
        });
    }

    /**
     * Cashier state
     */
    public function cashier()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => 'cashier',
                'role' => 'Cashier',
                'years_of_experience' => $this->faker->numberBetween(1, 10),
            ];
        });
    }

    /**
     * Pharmacist state
     */
    public function pharmacist()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => 'pharmacist',
                'role' => 'Pharmacist',
                'medical_license_number' => 'PH' . $this->faker->numberBetween(10000, 99999),
                'specialization' => 'Pharmacy',
                'years_of_experience' => $this->faker->numberBetween(3, 25),
            ];
        });
    }

    /**
     * Lab Technician state
     */
    public function labTechnician()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => 'lab_technician',
                'role' => 'Lab Technician',
                'medical_license_number' => 'LT' . $this->faker->numberBetween(10000, 99999),
                'specialization' => $this->faker->randomElement([
                    'Clinical Laboratory', 'Microbiology', 'Hematology', 'Biochemistry'
                ]),
                'years_of_experience' => $this->faker->numberBetween(2, 20),
            ];
        });
    }

    /**
     * Admin state
     */
    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => 'admin',
                'role' => 'Admin',
                'years_of_experience' => $this->faker->numberBetween(2, 20),
            ];
        });
    }

    /**
     * Active card status
     */
    public function activeCard()
    {
        return $this->state(function (array $attributes) {
            return [
                'card_status' => 'Active',
            ];
        });
    }

    /**
     * With comprehensive medical data
     */
    public function withMedicalData()
    {
        return $this->state(function (array $attributes) {
            return [
                'medical_history' => $this->faker->paragraph(2),
                'allergies' => $this->faker->words(3, true),
                'current_medications' => $this->faker->words(3, true),
                'insurance_provider' => $this->faker->company(),
                'insurance_policy_number' => 'POL-' . $this->faker->numerify('#######'),
                'insurance_expiry_date' => $this->faker->date('Y-m-d', '+1 year'),
                'family_doctor_name' => $this->faker->name(),
                'family_doctor_phone' => $this->faker->phoneNumber(),
            ];
        });
    }

    /**
     * Enterprise user
     */
    public function forEnterprise($enterpriseId)
    {
        return $this->state(function (array $attributes) use ($enterpriseId) {
            return [
                'enterprise_id' => $enterpriseId,
            ];
        });
    }
}
