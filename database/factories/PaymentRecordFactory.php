<?php

namespace Database\Factories;

use App\Models\PaymentRecord;
use App\Models\Consultation;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentRecord>
 */
class PaymentRecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentMethods = ['Cash', 'Card', 'Mobile Money', 'Flutterwave', 'Bank Transfer'];
        $paymentMethod = $this->faker->randomElement($paymentMethods);
        
        $amountPayable = $this->faker->randomFloat(0, 50000, 1000000);
        $amountPaid = $this->faker->randomFloat(0, 
            $amountPayable * 0.5, // Minimum 50% of payable
            $amountPayable * 1.2   // Maximum 120% (overpayment)
        );
        $balance = $amountPayable - $amountPaid;

        $paymentDate = $this->faker->dateTimeBetween('-6 months', 'now');

        return [
            'enterprise_id' => Company::factory(),
            'consultation_id' => Consultation::factory(),
            'description' => $this->getPaymentDescription(),
            
            // Financial Information
            'amount_payable' => $amountPayable,
            'amount_paid' => $amountPaid,
            'balance' => $balance,
            
            // Payment Details
            'payment_date' => $paymentDate,
            'payment_time' => $paymentDate,
            'payment_method' => $paymentMethod,
            'payment_reference' => $this->getPaymentReference($paymentMethod),
            'payment_status' => $this->getPaymentStatus($paymentMethod),
            'payment_remarks' => $this->faker->optional()->sentence(),
            'payment_phone_number' => $paymentMethod === 'Mobile Money' ? $this->faker->phoneNumber() : null,
            'payment_channel' => $this->getPaymentChannel($paymentMethod),
            
            // Staff Information
            'cash_received_by_id' => $paymentMethod === 'Cash' ? User::factory()->cashier() : null,
            'created_by_id' => User::factory()->receptionist(),
            'cash_receipt_number' => $paymentMethod === 'Cash' ? 'CR' . $this->faker->unique()->numberBetween(100000, 999999) : null,
            
            // Card Information
            'card_id' => $paymentMethod === 'Card' ? User::factory() : null,
            'card_number' => $paymentMethod === 'Card' ? '**** **** **** ' . $this->faker->numberBetween(1000, 9999) : null,
            'card_type' => $paymentMethod === 'Card' ? $this->faker->randomElement(['Visa', 'Mastercard', 'American Express']) : null,
            
            // Flutterwave Information
            'flutterwave_reference' => $paymentMethod === 'Flutterwave' ? 'FLW_' . $this->faker->uuid() : null,
            'flutterwave_payment_type' => $paymentMethod === 'Flutterwave' ? 'card' : null,
            'flutterwave_payment_status' => $paymentMethod === 'Flutterwave' ? 'successful' : null,
            'flutterwave_payment_message' => $paymentMethod === 'Flutterwave' ? 'Payment successful' : null,
            'flutterwave_payment_code' => $paymentMethod === 'Flutterwave' ? '200' : null,
            'flutterwave_payment_amount' => $paymentMethod === 'Flutterwave' ? $amountPaid : null,
            'flutterwave_payment_customer_name' => $paymentMethod === 'Flutterwave' ? $this->faker->name() : null,
            'flutterwave_payment_customer_email' => $paymentMethod === 'Flutterwave' ? $this->faker->email() : null,
            'flutterwave_payment_customer_phone_number' => $paymentMethod === 'Flutterwave' ? $this->faker->phoneNumber() : null,
            
            'created_at' => $paymentDate,
            'updated_at' => $this->faker->dateTimeBetween($paymentDate, 'now'),
        ];
    }

    /**
     * Get payment description.
     */
    private function getPaymentDescription(): string
    {
        return $this->faker->randomElement([
            'Payment for medical consultation',
            'Payment for laboratory tests',
            'Payment for radiology services',
            'Payment for pharmacy items',
            'Payment for surgical procedure',
            'Payment for specialist consultation',
            'Payment for emergency services',
            'Payment for admission fees',
            'Payment for physiotherapy',
            'Payment for nursing care'
        ]);
    }

    /**
     * Get payment reference based on method.
     */
    private function getPaymentReference(string $method): ?string
    {
        switch ($method) {
            case 'Mobile Money':
                return 'MM' . $this->faker->numberBetween(1000000000, 9999999999);
            case 'Card':
                return 'TXN' . $this->faker->numberBetween(100000000, 999999999);
            case 'Flutterwave':
                return 'FLW_' . $this->faker->uuid();
            case 'Bank Transfer':
                return 'BT' . $this->faker->numberBetween(100000000, 999999999);
            case 'Cash':
                return 'CASH' . $this->faker->numberBetween(100000, 999999);
            default:
                return null;
        }
    }

    /**
     * Get payment status based on method.
     */
    private function getPaymentStatus(string $method): string
    {
        if ($method === 'Cash') {
            return 'Success';
        }
        
        return $this->faker->randomElement(['Success', 'Pending', 'Failed']);
    }

    /**
     * Get payment channel based on method.
     */
    private function getPaymentChannel(string $method): ?string
    {
        switch ($method) {
            case 'Mobile Money':
                return $this->faker->randomElement(['MTN Mobile Money', 'Airtel Money', 'M-Sente']);
            case 'Card':
                return $this->faker->randomElement(['POS Terminal', 'Online Payment', 'ATM']);
            case 'Flutterwave':
                return 'Flutterwave Gateway';
            case 'Bank Transfer':
                return $this->faker->randomElement(['SWIFT Transfer', 'Local Bank Transfer', 'Online Banking']);
            case 'Cash':
                return 'Cash Counter';
            default:
                return null;
        }
    }

    /**
     * Indicate that the payment is successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'Success',
            'balance' => 0,
            'amount_paid' => $attributes['amount_payable'],
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'Pending',
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'Failed',
            'amount_paid' => 0,
            'balance' => $attributes['amount_payable'],
        ]);
    }

    /**
     * Indicate that the payment is partial.
     */
    public function partial(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_paid' => $attributes['amount_payable'] * $this->faker->randomFloat(2, 0.2, 0.8),
            'balance' => function (array $attributes) {
                return $attributes['amount_payable'] - $attributes['amount_paid'];
            },
        ]);
    }

    /**
     * Indicate that the payment is an overpayment.
     */
    public function overpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_paid' => $attributes['amount_payable'] * $this->faker->randomFloat(2, 1.1, 1.5),
            'balance' => function (array $attributes) {
                return $attributes['amount_payable'] - $attributes['amount_paid']; // Negative balance
            },
        ]);
    }

    /**
     * Indicate that the payment is cash.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'Cash',
            'payment_status' => 'Success',
            'cash_received_by_id' => User::factory()->cashier(),
            'cash_receipt_number' => 'CR' . $this->faker->unique()->numberBetween(100000, 999999),
            'payment_reference' => 'CASH' . $this->faker->numberBetween(100000, 999999),
            'payment_channel' => 'Cash Counter',
        ]);
    }

    /**
     * Indicate that the payment is via mobile money.
     */
    public function mobileMoney(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'Mobile Money',
            'payment_phone_number' => $this->faker->phoneNumber(),
            'payment_reference' => 'MM' . $this->faker->numberBetween(1000000000, 9999999999),
            'payment_channel' => $this->faker->randomElement(['MTN Mobile Money', 'Airtel Money', 'M-Sente']),
        ]);
    }

    /**
     * Indicate that the payment is via card.
     */
    public function card(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'Card',
            'card_id' => User::factory(),
            'card_number' => '**** **** **** ' . $this->faker->numberBetween(1000, 9999),
            'card_type' => $this->faker->randomElement(['Visa', 'Mastercard', 'American Express']),
            'payment_reference' => 'TXN' . $this->faker->numberBetween(100000000, 999999999),
            'payment_channel' => $this->faker->randomElement(['POS Terminal', 'Online Payment']),
        ]);
    }

    /**
     * Indicate that the payment is via Flutterwave.
     */
    public function flutterwave(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'Flutterwave',
            'flutterwave_reference' => 'FLW_' . $this->faker->uuid(),
            'flutterwave_payment_type' => 'card',
            'flutterwave_payment_status' => 'successful',
            'flutterwave_payment_message' => 'Payment successful',
            'flutterwave_payment_code' => '200',
            'flutterwave_payment_amount' => $attributes['amount_paid'],
            'flutterwave_payment_customer_name' => $this->faker->name(),
            'flutterwave_payment_customer_email' => $this->faker->email(),
            'flutterwave_payment_customer_phone_number' => $this->faker->phoneNumber(),
            'payment_channel' => 'Flutterwave Gateway',
        ]);
    }

    /**
     * Indicate that the payment is for a specific consultation.
     */
    public function forConsultation(Consultation $consultation): static
    {
        return $this->state(fn (array $attributes) => [
            'consultation_id' => $consultation->id,
            'enterprise_id' => $consultation->enterprise_id,
            'amount_payable' => $consultation->total_charges ?? $this->faker->randomFloat(0, 50000, 500000),
        ]);
    }

    /**
     * Indicate that the payment is for a specific enterprise.
     */
    public function forEnterprise(Company $enterprise): static
    {
        return $this->state(fn (array $attributes) => [
            'enterprise_id' => $enterprise->id,
        ]);
    }

    /**
     * Indicate that the payment was created today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_date' => Carbon::today(),
            'payment_time' => Carbon::now(),
            'created_at' => Carbon::today(),
        ]);
    }

    /**
     * Indicate that the payment was created this month.
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_date' => $this->faker->dateTimeBetween('first day of this month', 'now'),
            'created_at' => function (array $attributes) {
                return $attributes['payment_date'];
            },
        ]);
    }

    /**
     * Indicate that the payment has high value.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_payable' => $this->faker->randomFloat(0, 500000, 2000000),
            'amount_paid' => function (array $attributes) {
                return $this->faker->randomFloat(0, 
                    $attributes['amount_payable'] * 0.5, 
                    $attributes['amount_payable']
                );
            },
        ]);
    }
}
