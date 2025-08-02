<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
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
            'name' => $this->faker->company(),
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'address' => $this->faker->address(),
            'website' => $this->faker->optional()->url(),
            'industry' => $this->faker->randomElement(['Healthcare', 'Education', 'Technology', 'Manufacturing', 'Finance', 'Retail']),
            'status' => $this->faker->randomElement(['Active', 'Inactive']),
            'contact_person_name' => $this->faker->name(),
            'contact_person_phone' => $this->faker->phoneNumber(),
            'contact_person_email' => $this->faker->email(),
            'registration_number' => $this->faker->numerify('REG-#######'),
            'tax_number' => $this->faker->numerify('TAX-#######'),
            'contract_start_date' => $this->faker->date('Y-m-d', '-1 year'),
            'contract_end_date' => $this->faker->date('Y-m-d', '+1 year'),
            'details' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Active company state
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Active',
            ];
        });
    }

    /**
     * Inactive company state
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Inactive',
            ];
        });
    }
}
