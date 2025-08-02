<?php

namespace Database\Factories;

use App\Models\BillingItem;
use App\Models\Consultation;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BillingItem>
 */
class BillingItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BillingItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $billingTypes = [
            'consultation' => 'Consultation Fee',
            'laboratory' => 'Laboratory Test',
            'radiology' => 'Radiology/Imaging',
            'pharmacy' => 'Pharmacy',
            'procedure' => 'Medical Procedure',
            'surgery' => 'Surgery',
            'admission' => 'Admission Fee',
            'bed_charge' => 'Bed Charge',
            'nursing' => 'Nursing Care',
            'physiotherapy' => 'Physiotherapy',
            'specialist' => 'Specialist Consultation',
            'emergency' => 'Emergency Service',
            'ambulance' => 'Ambulance Service',
            'other' => 'Other',
        ];

        $type = $this->faker->randomKey($billingTypes);

        return [
            'enterprise_id' => Company::factory(),
            'consultation_id' => Consultation::factory(),
            'type' => $type,
            'description' => $this->getBillingDescription($type),
            'price' => $this->getBillingPrice($type),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Get billing description based on type.
     */
    private function getBillingDescription(string $type): string
    {
        $descriptions = [
            'consultation' => $this->faker->randomElement([
                'General medical consultation',
                'Follow-up consultation',
                'Initial consultation',
                'Emergency consultation',
                'Routine check-up'
            ]),
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
                'HIV test',
                'Malaria test',
                'Hepatitis B test',
                'Pregnancy test'
            ]),
            'radiology' => $this->faker->randomElement([
                'Chest X-ray',
                'Abdominal X-ray',
                'Pelvic ultrasound',
                'Abdominal ultrasound',
                'CT scan - head',
                'CT scan - chest',
                'MRI - brain',
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
                'Allergy medication',
                'Cough syrup',
                'Eye drops',
                'Ointment'
            ]),
            'procedure' => $this->faker->randomElement([
                'Minor surgery',
                'Wound suturing',
                'Injection therapy',
                'Endoscopy',
                'Colonoscopy',
                'Biopsy',
                'Catheter insertion',
                'Dressing change'
            ]),
            'surgery' => $this->faker->randomElement([
                'Appendectomy',
                'Gallbladder removal',
                'Hernia repair',
                'Cataract surgery',
                'Tonsillectomy',
                'Cesarean section',
                'Fracture fixation'
            ]),
            'admission' => $this->faker->randomElement([
                'General ward admission',
                'Private room admission',
                'ICU admission',
                'Emergency admission',
                'Day care admission'
            ]),
            'bed_charge' => $this->faker->randomElement([
                'General ward bed - per day',
                'Private room bed - per day',
                'ICU bed - per day',
                'Semi-private room bed - per day'
            ]),
            'nursing' => $this->faker->randomElement([
                'Nursing care - 8 hours',
                'Nursing care - 12 hours',
                'Special nursing care',
                'Post-operative nursing',
                'Critical care nursing'
            ]),
            'physiotherapy' => $this->faker->randomElement([
                'Post-surgery rehabilitation',
                'Back pain therapy',
                'Sports injury treatment',
                'Stroke rehabilitation',
                'Joint mobility therapy'
            ]),
            'specialist' => $this->faker->randomElement([
                'Cardiology consultation',
                'Neurology consultation',
                'Orthopedic consultation',
                'Dermatology consultation',
                'Gynecology consultation',
                'Pediatric consultation',
                'Psychiatric consultation'
            ]),
            'emergency' => $this->faker->randomElement([
                'Emergency room consultation',
                'Trauma care',
                'Emergency surgery',
                'Resuscitation',
                'Emergency medication'
            ]),
            'ambulance' => $this->faker->randomElement([
                'Basic ambulance service',
                'Advanced life support ambulance',
                'Emergency transport',
                'Inter-facility transport'
            ]),
            'other' => $this->faker->sentence()
        ];

        return $descriptions[$type] ?? $this->faker->sentence();
    }

    /**
     * Get billing price based on type.
     */
    private function getBillingPrice(string $type): float
    {
        $priceRanges = [
            'consultation' => [20000, 100000],
            'laboratory' => [5000, 50000],
            'radiology' => [30000, 150000],
            'pharmacy' => [2000, 50000],
            'procedure' => [50000, 300000],
            'surgery' => [200000, 2000000],
            'admission' => [50000, 200000],
            'bed_charge' => [30000, 150000],
            'nursing' => [40000, 120000],
            'physiotherapy' => [25000, 80000],
            'specialist' => [50000, 200000],
            'emergency' => [40000, 300000],
            'ambulance' => [30000, 100000],
            'other' => [10000, 100000],
        ];

        $range = $priceRanges[$type] ?? [10000, 100000];
        return $this->faker->randomFloat(0, $range[0], $range[1]);
    }

    /**
     * Indicate that the billing item is for consultation.
     */
    public function consultation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'consultation',
            'description' => $this->getBillingDescription('consultation'),
            'price' => $this->getBillingPrice('consultation'),
        ]);
    }

    /**
     * Indicate that the billing item is for laboratory.
     */
    public function laboratory(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'laboratory',
            'description' => $this->getBillingDescription('laboratory'),
            'price' => $this->getBillingPrice('laboratory'),
        ]);
    }

    /**
     * Indicate that the billing item is for radiology.
     */
    public function radiology(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'radiology',
            'description' => $this->getBillingDescription('radiology'),
            'price' => $this->getBillingPrice('radiology'),
        ]);
    }

    /**
     * Indicate that the billing item is for pharmacy.
     */
    public function pharmacy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pharmacy',
            'description' => $this->getBillingDescription('pharmacy'),
            'price' => $this->getBillingPrice('pharmacy'),
        ]);
    }

    /**
     * Indicate that the billing item is for surgery.
     */
    public function surgery(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'surgery',
            'description' => $this->getBillingDescription('surgery'),
            'price' => $this->getBillingPrice('surgery'),
        ]);
    }

    /**
     * Indicate that the billing item is an emergency service.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'emergency',
            'description' => $this->getBillingDescription('emergency'),
            'price' => $this->getBillingPrice('emergency'),
        ]);
    }

    /**
     * Indicate that the billing item is for a specific consultation.
     */
    public function forConsultation(Consultation $consultation): static
    {
        return $this->state(fn (array $attributes) => [
            'consultation_id' => $consultation->id,
            'enterprise_id' => $consultation->enterprise_id,
        ]);
    }

    /**
     * Indicate that the billing item is for a specific enterprise.
     */
    public function forEnterprise(Company $enterprise): static
    {
        return $this->state(fn (array $attributes) => [
            'enterprise_id' => $enterprise->id,
        ]);
    }

    /**
     * Indicate that the billing item has high value pricing.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(0, 500000, 2000000),
        ]);
    }

    /**
     * Indicate that the billing item has low value pricing.
     */
    public function lowValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(0, 1000, 50000),
        ]);
    }

    /**
     * Indicate that the billing item is expensive.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(0, 1000000, 5000000),
        ]);
    }

    /**
     * Indicate that the billing item is cheap.
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(0, 1000, 20000),
        ]);
    }

    /**
     * Create a billing item for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => Carbon::today(),
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Create a billing item for this month.
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('first day of this month', 'now'),
            'updated_at' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
        ]);
    }
}
