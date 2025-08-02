<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Consultation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-1 year', 'now');
        
        return [
            'enterprise_id' => Company::factory(),
            'patient_id' => User::factory()->patient(),
            'receptionist_id' => User::factory()->receptionist(),
            'main_status' => $this->faker->randomElement(['Pending', 'Ongoing', 'Billing', 'Payment', 'Completed']),
            'patient_name' => $this->faker->name(),
            'patient_contact' => $this->faker->phoneNumber(),
            'contact_address' => $this->faker->address(),
            'consultation_number' => 'CON' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'preferred_date_and_time' => $this->faker->dateTimeBetween($createdAt, '+1 month'),
            'services_requested' => $this->faker->randomElements([
                'General Consultation',
                'Specialist Consultation', 
                'Laboratory Tests',
                'Radiology',
                'Pharmacy',
                'Physiotherapy'
            ], $this->faker->numberBetween(1, 3)),
            'reason_for_consultation' => $this->faker->paragraph(2),
            'main_remarks' => $this->faker->optional()->paragraph(),
            'request_status' => $this->faker->randomElement(['Pending', 'Approved', 'Rejected']),
            'request_date' => $createdAt,
            'request_remarks' => $this->faker->optional()->sentence(),
            'receptionist_comment' => $this->faker->optional()->paragraph(),
            
            // Vital Signs
            'temperature' => $this->faker->randomFloat(1, 36.0, 39.5),
            'weight' => $this->faker->randomFloat(1, 40.0, 120.0),
            'height' => $this->faker->randomFloat(2, 1.40, 2.00),
            'blood_pressure_systolic' => $this->faker->numberBetween(90, 180),
            'blood_pressure_diastolic' => $this->faker->numberBetween(60, 120),
            'pulse_rate' => $this->faker->numberBetween(60, 120),
            'respiratory_rate' => $this->faker->numberBetween(12, 30),
            'blood_sugar_level' => $this->faker->optional()->randomFloat(1, 70.0, 200.0),
            'oxygen_saturation' => $this->faker->randomFloat(1, 95.0, 100.0),
            
            // Medical History
            'allergies' => $this->faker->optional()->randomElements([
                'Penicillin', 'Peanuts', 'Shellfish', 'Latex', 'Dust'
            ], $this->faker->numberBetween(0, 2)),
            'current_medications' => $this->faker->optional()->paragraph(),
            'medical_history' => $this->faker->optional()->paragraph(),
            'family_medical_history' => $this->faker->optional()->paragraph(),
            'social_history' => $this->faker->optional()->paragraph(),
            
            // Diagnosis & Treatment
            'chief_complaint' => $this->faker->sentence(),
            'present_illness_history' => $this->faker->paragraph(),
            'examination_findings' => $this->faker->optional()->paragraph(),
            'diagnosis' => $this->faker->optional()->sentence(),
            'treatment_plan' => $this->faker->optional()->paragraph(),
            'prescribed_medications' => $this->faker->optional()->paragraph(),
            'follow_up_instructions' => $this->faker->optional()->paragraph(),
            'next_appointment_date' => $this->faker->optional()->dateTimeBetween('+1 week', '+1 month'),
            
            // Billing Information
            'total_charges' => $this->faker->randomFloat(0, 50000, 500000),
            'total_paid' => function (array $attributes) {
                return $this->faker->randomFloat(0, 0, $attributes['total_charges']);
            },
            'bill_status' => $this->faker->randomElement(['Pending', 'Ready for Billing', 'Billed', 'Paid']),
            'payment_status' => $this->faker->randomElement(['Pending', 'Partial', 'Paid', 'Overpaid']),
            
            // Additional Medical Fields
            'services_text' => $this->faker->optional()->paragraph(),
            'patient_signature' => $this->faker->optional()->imageUrl(200, 100, 'signature'),
            'doctor_signature' => $this->faker->optional()->imageUrl(200, 100, 'signature'),
            'consultation_notes' => $this->faker->optional()->paragraph(3),
            'referral_notes' => $this->faker->optional()->paragraph(),
            'discharge_summary' => $this->faker->optional()->paragraph(),
            
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
        ];
    }

    /**
     * Indicate that the consultation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'main_status' => 'Pending',
            'request_status' => 'Pending',
            'bill_status' => 'Pending',
            'payment_status' => 'Pending',
        ]);
    }

    /**
     * Indicate that the consultation is ongoing.
     */
    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'main_status' => 'Ongoing',
            'request_status' => 'Approved',
            'bill_status' => 'Pending',
        ]);
    }

    /**
     * Indicate that the consultation is ready for billing.
     */
    public function readyForBilling(): static
    {
        return $this->state(fn (array $attributes) => [
            'main_status' => 'Billing',
            'bill_status' => 'Ready for Billing',
            'diagnosis' => $this->faker->sentence(),
            'treatment_plan' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the consultation is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'main_status' => 'Completed',
            'bill_status' => 'Billed',
            'payment_status' => 'Paid',
            'diagnosis' => $this->faker->sentence(),
            'treatment_plan' => $this->faker->paragraph(),
            'discharge_summary' => $this->faker->paragraph(),
            'total_paid' => $attributes['total_charges'],
        ]);
    }

    /**
     * Indicate that the consultation has emergency priority.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'reason_for_consultation' => 'Emergency: ' . $this->faker->randomElement([
                'Chest pain', 'Difficulty breathing', 'Severe injury', 'Unconscious patient'
            ]),
            'chief_complaint' => 'Emergency case',
            'main_status' => 'Ongoing',
            'request_status' => 'Approved',
        ]);
    }

    /**
     * Indicate that the consultation is for a specific enterprise.
     */
    public function forEnterprise(Company $enterprise): static
    {
        return $this->state(fn (array $attributes) => [
            'enterprise_id' => $enterprise->id,
        ]);
    }

    /**
     * Indicate that the consultation is for a specific patient.
     */
    public function forPatient(User $patient): static
    {
        return $this->state(fn (array $attributes) => [
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'patient_contact' => $patient->phone,
        ]);
    }

    /**
     * Indicate that the consultation has comprehensive medical data.
     */
    public function withComprehensiveData(): static
    {
        return $this->state(fn (array $attributes) => [
            'allergies' => $this->faker->randomElements([
                'Penicillin', 'Peanuts', 'Shellfish', 'Latex', 'Dust', 'Pollen'
            ], $this->faker->numberBetween(1, 3)),
            'current_medications' => $this->faker->paragraph(),
            'medical_history' => $this->faker->paragraph(2),
            'family_medical_history' => $this->faker->paragraph(),
            'examination_findings' => $this->faker->paragraph(),
            'diagnosis' => $this->faker->sentence(),
            'treatment_plan' => $this->faker->paragraph(),
            'prescribed_medications' => $this->faker->paragraph(),
            'follow_up_instructions' => $this->faker->paragraph(),
            'consultation_notes' => $this->faker->paragraph(3),
        ]);
    }
}
