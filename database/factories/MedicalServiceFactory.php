<?php

namespace Database\Factories;

use App\Models\MedicalService;
use App\Models\Consultation;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalService>
 */
class MedicalServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MedicalService::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serviceTypes = [
            'laboratory' => 'Laboratory Test',
            'radiology' => 'Radiology/Imaging',
            'pharmacy' => 'Pharmacy',
            'physiotherapy' => 'Physiotherapy',
            'nursing' => 'Nursing Care',
            'specialist' => 'Specialist Consultation',
            'surgery' => 'Surgery',
            'procedure' => 'Medical Procedure',
            'other' => 'Other',
        ];

        $type = $this->faker->randomKey($serviceTypes);
        $unitPrice = $this->faker->randomFloat(0, 10000, 200000);
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'enterprise_id' => Company::factory(),
            'consultation_id' => Consultation::factory(),
            'patient_id' => function (array $attributes) {
                $consultation = Consultation::find($attributes['consultation_id']);
                return $consultation ? $consultation->patient_id : User::factory()->patient();
            },
            'receptionist_id' => User::factory()->receptionist(),
            'assigned_to_id' => User::factory()->doctor(),
            
            'type' => $type,
            'description' => $this->getServiceDescription($type),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            
            // Pricing
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $unitPrice * $quantity,
            
            // Service Details
            'instruction' => $this->faker->paragraph(),
            'remarks' => $this->faker->optional()->paragraph(),
            'specialist_outcome' => $this->faker->optional()->paragraph(),
            
            // Files and Documentation
            'file' => $this->faker->optional()->word() . '.pdf',
            'file_url' => $this->faker->optional()->url(),
            
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Get service description based on type.
     */
    private function getServiceDescription(string $type): string
    {
        $descriptions = [
            'laboratory' => $this->faker->randomElement([
                'Complete Blood Count (CBC)',
                'Blood glucose test',
                'Liver function test',
                'Kidney function test',
                'Lipid profile',
                'Thyroid function test',
                'Urinalysis',
                'Stool examination',
                'Blood culture',
                'HIV test'
            ]),
            'radiology' => $this->faker->randomElement([
                'Chest X-ray',
                'Abdominal ultrasound',
                'CT scan - head',
                'MRI - spine',
                'Mammography',
                'Bone densitometry',
                'Echocardiogram',
                'Doppler ultrasound'
            ]),
            'pharmacy' => $this->faker->randomElement([
                'Antibiotic prescription',
                'Pain medication',
                'Blood pressure medication',
                'Diabetes medication',
                'Vitamin supplements',
                'Antacid medication',
                'Allergy medication'
            ]),
            'physiotherapy' => $this->faker->randomElement([
                'Post-surgery rehabilitation',
                'Back pain therapy',
                'Sports injury treatment',
                'Stroke rehabilitation',
                'Joint mobility therapy'
            ]),
            'nursing' => $this->faker->randomElement([
                'Wound dressing',
                'Injection administration',
                'Vital signs monitoring',
                'Patient education',
                'Medication administration'
            ]),
            'specialist' => $this->faker->randomElement([
                'Cardiology consultation',
                'Neurology consultation',
                'Orthopedic consultation',
                'Dermatology consultation',
                'Gynecology consultation'
            ]),
            'surgery' => $this->faker->randomElement([
                'Appendectomy',
                'Gallbladder removal',
                'Hernia repair',
                'Cataract surgery',
                'Minor surgery'
            ]),
            'procedure' => $this->faker->randomElement([
                'Endoscopy',
                'Colonoscopy',
                'Biopsy',
                'Injection therapy',
                'Minor procedures'
            ]),
            'other' => $this->faker->sentence()
        ];

        return $descriptions[$type] ?? $this->faker->sentence();
    }

    /**
     * Indicate that the service is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'specialist_outcome' => null,
        ]);
    }

    /**
     * Indicate that the service is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'specialist_outcome' => null,
        ]);
    }

    /**
     * Indicate that the service is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'specialist_outcome' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the service is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'remarks' => 'Cancelled: ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the service is for laboratory.
     */
    public function laboratory(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'laboratory',
            'description' => $this->getServiceDescription('laboratory'),
            'unit_price' => $this->faker->randomFloat(0, 5000, 50000),
        ]);
    }

    /**
     * Indicate that the service is for radiology.
     */
    public function radiology(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'radiology',
            'description' => $this->getServiceDescription('radiology'),
            'unit_price' => $this->faker->randomFloat(0, 20000, 100000),
        ]);
    }

    /**
     * Indicate that the service is for pharmacy.
     */
    public function pharmacy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pharmacy',
            'description' => $this->getServiceDescription('pharmacy'),
            'unit_price' => $this->faker->randomFloat(0, 2000, 30000),
        ]);
    }

    /**
     * Indicate that the service is for surgery.
     */
    public function surgery(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'surgery',
            'description' => $this->getServiceDescription('surgery'),
            'unit_price' => $this->faker->randomFloat(0, 100000, 1000000),
            'assigned_to_id' => User::factory()->surgeon(),
        ]);
    }

    /**
     * Indicate that the service is for a specific consultation.
     */
    public function forConsultation(Consultation $consultation): static
    {
        return $this->state(fn (array $attributes) => [
            'consultation_id' => $consultation->id,
            'patient_id' => $consultation->patient_id,
            'enterprise_id' => $consultation->enterprise_id,
        ]);
    }

    /**
     * Indicate that the service is for a specific enterprise.
     */
    public function forEnterprise(Company $enterprise): static
    {
        return $this->state(fn (array $attributes) => [
            'enterprise_id' => $enterprise->id,
        ]);
    }

    /**
     * Indicate that the service is assigned to a specific medical professional.
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the service has high value pricing.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_price' => $this->faker->randomFloat(0, 200000, 1000000),
            'total_price' => function (array $attributes) {
                return $attributes['unit_price'] * $attributes['quantity'];
            },
        ]);
    }

    /**
     * Indicate that the service has comprehensive documentation.
     */
    public function withDocumentation(): static
    {
        return $this->state(fn (array $attributes) => [
            'instruction' => $this->faker->paragraph(2),
            'remarks' => $this->faker->paragraph(),
            'specialist_outcome' => $this->faker->paragraph(2),
            'file' => 'medical_report_' . $this->faker->uuid() . '.pdf',
            'file_url' => $this->faker->url(),
        ]);
    }
}
