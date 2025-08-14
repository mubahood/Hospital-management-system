<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Enterprise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Department::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enterprise_id' => Enterprise::factory(),
            'name' => $this->faker->randomElement([
                'General Medicine',
                'Pediatrics',
                'Surgery',
                'Emergency Department',
                'Cardiology',
                'Neurology',
                'Oncology',
                'Orthopedics',
                'Radiology',
                'Laboratory',
                'Pharmacy',
                'Nursing',
                'Administration',
                'ICU',
                'Maternity'
            ]),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'head_of_department' => $this->faker->name(),
            'location' => $this->faker->randomElement([
                'Building A - Floor 1',
                'Building A - Floor 2', 
                'Building B - Floor 1',
                'Building B - Floor 2',
                'Building C - Ground Floor',
                'Main Building - Ground Floor',
                'Emergency Wing',
                'Outpatient Building'
            ]),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'capacity' => $this->faker->numberBetween(10, 100),
            'budget' => $this->faker->numberBetween(50000, 1000000),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Create a department for a specific enterprise
     */
    public function forEnterprise($enterpriseId): static
    {
        return $this->state(fn (array $attributes) => [
            'enterprise_id' => $enterpriseId,
        ]);
    }

    /**
     * Create an active department
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Create an inactive department
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Create a medical department
     */
    public function medical(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'General Medicine',
                'Pediatrics',
                'Surgery',
                'Cardiology',
                'Neurology',
                'Oncology',
                'Orthopedics'
            ]),
        ]);
    }

    /**
     * Create a support department
     */
    public function support(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Administration',
                'Human Resources',
                'Finance',
                'IT Support',
                'Maintenance',
                'Security'
            ]),
        ]);
    }
}
