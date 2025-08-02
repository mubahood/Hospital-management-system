<?php

namespace Tests\Feature;

use App\Models\BillingItem;
use App\Models\Company;
use App\Models\Consultation;
use App\Models\MedicalService;
use App\Models\PaymentRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ModelFactoriesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure we have a basic enterprise for all tests
        $this->enterprise = Company::factory()->create();
    }

    /** @test */
    public function it_can_create_users_with_different_roles()
    {
        // Test patient creation
        $patient = User::factory()
            ->patient()
            ->forEnterprise($this->enterprise->id)
            ->create();

        $this->assertEquals('patient', $patient->user_type);
        $this->assertEquals('Patient', $patient->role);
        $this->assertEquals($this->enterprise->id, $patient->enterprise_id);

        // Test doctor creation
        $doctor = User::factory()
            ->doctor()
            ->forEnterprise($this->enterprise->id)
            ->create();

        $this->assertEquals('doctor', $doctor->user_type);
        $this->assertEquals('Doctor', $doctor->role);
        $this->assertNotNull($doctor->medical_license_number);
        $this->assertNotNull($doctor->specialization);

        // Test specialist creation
        $specialist = User::factory()
            ->specialist()
            ->forEnterprise($this->enterprise->id)
            ->create();

        $this->assertEquals('specialist', $specialist->user_type);
        $this->assertEquals('Specialist', $specialist->role);
        $this->assertStringStartsWith('SP', $specialist->medical_license_number);

        // Test nurse creation
        $nurse = User::factory()
            ->nurse()
            ->forEnterprise($this->enterprise->id)
            ->create();

        $this->assertEquals('nurse', $nurse->user_type);
        $this->assertEquals('Nurse', $nurse->role);
        $this->assertStringStartsWith('RN', $nurse->medical_license_number);
    }

    /** @test */
    public function it_can_create_consultations_with_different_states()
    {
        $patient = User::factory()->patient()->forEnterprise($this->enterprise->id)->create();
        $receptionist = User::factory()->receptionist()->forEnterprise($this->enterprise->id)->create();

        // Test pending consultation
        $pendingConsultation = Consultation::factory()
            ->pending()
            ->forEnterprise($this->enterprise)
            ->forPatient($patient)
            ->create(['receptionist_id' => $receptionist->id]);

        $this->assertEquals('Pending', $pendingConsultation->main_status);
        $this->assertEquals('Pending', $pendingConsultation->request_status);
        $this->assertEquals($patient->id, $pendingConsultation->patient_id);

        // Test ongoing consultation
        $ongoingConsultation = Consultation::factory()
            ->ongoing()
            ->forEnterprise($this->enterprise)
            ->create();

        $this->assertEquals('Ongoing', $ongoingConsultation->main_status);
        $this->assertEquals('Approved', $ongoingConsultation->request_status);

        // Test completed consultation
        $completedConsultation = Consultation::factory()
            ->completed()
            ->forEnterprise($this->enterprise)
            ->create();

        $this->assertEquals('Completed', $completedConsultation->main_status);
        $this->assertEquals('Paid', $completedConsultation->payment_status);
        $this->assertNotNull($completedConsultation->diagnosis);
        $this->assertEquals($completedConsultation->total_charges, $completedConsultation->total_paid);
    }

    /** @test */
    public function it_can_create_medical_services_with_different_types()
    {
        $consultation = Consultation::factory()
            ->forEnterprise($this->enterprise)
            ->create();
        
        $doctor = User::factory()->doctor()->forEnterprise($this->enterprise->id)->create();

        // Test laboratory service
        $labService = MedicalService::factory()
            ->laboratory()
            ->forConsultation($consultation)
            ->assignedTo($doctor)
            ->create();

        $this->assertEquals('laboratory', $labService->type);
        $this->assertEquals($consultation->id, $labService->consultation_id);
        $this->assertEquals($doctor->id, $labService->assigned_to_id);
        $this->assertGreaterThan(0, $labService->unit_price);

        // Test surgery service
        $surgeon = User::factory()->surgeon()->forEnterprise($this->enterprise->id)->create();
        $surgeryService = MedicalService::factory()
            ->surgery()
            ->forConsultation($consultation)
            ->assignedTo($surgeon)
            ->create();

        $this->assertEquals('surgery', $surgeryService->type);
        $this->assertEquals($surgeon->id, $surgeryService->assigned_to_id);
        $this->assertGreaterThan(100000, $surgeryService->unit_price); // Surgery should be expensive

        // Test completed service
        $completedService = MedicalService::factory()
            ->completed()
            ->withDocumentation()
            ->forConsultation($consultation)
            ->create();

        $this->assertEquals('completed', $completedService->status);
        $this->assertNotNull($completedService->specialist_outcome);
        $this->assertNotNull($completedService->file);
    }

    /** @test */
    public function it_can_create_billing_items_with_different_types()
    {
        $consultation = Consultation::factory()
            ->forEnterprise($this->enterprise)
            ->create();

        // Test consultation billing
        $consultationBilling = BillingItem::factory()
            ->consultation()
            ->forConsultation($consultation)
            ->create();

        $this->assertEquals('consultation', $consultationBilling->type);
        $this->assertEquals($consultation->id, $consultationBilling->consultation_id);
        $this->assertBetween($consultationBilling->price, 20000, 100000);

        // Test laboratory billing
        $labBilling = BillingItem::factory()
            ->laboratory()
            ->forConsultation($consultation)
            ->create();

        $this->assertEquals('laboratory', $labBilling->type);
        $this->assertBetween($labBilling->price, 5000, 50000);

        // Test surgery billing (high value)
        $surgeryBilling = BillingItem::factory()
            ->surgery()
            ->forConsultation($consultation)
            ->create();

        $this->assertEquals('surgery', $surgeryBilling->type);
        $this->assertBetween($surgeryBilling->price, 200000, 2000000);

        // Test high value billing
        $expensiveBilling = BillingItem::factory()
            ->expensive()
            ->forConsultation($consultation)
            ->create();

        $this->assertGreaterThan(1000000, $expensiveBilling->price);
    }

    /** @test */
    public function it_can_create_payment_records_with_different_methods()
    {
        $consultation = Consultation::factory()
            ->forEnterprise($this->enterprise)
            ->create(['total_charges' => 100000]);

        $cashier = User::factory()->cashier()->forEnterprise($this->enterprise->id)->create();
        $receptionist = User::factory()->receptionist()->forEnterprise($this->enterprise->id)->create();

        // Test cash payment
        $cashPayment = PaymentRecord::factory()
            ->cash()
            ->successful()
            ->forConsultation($consultation)
            ->create(['created_by_id' => $receptionist->id]);

        $this->assertEquals('Cash', $cashPayment->payment_method);
        $this->assertEquals('Success', $cashPayment->payment_status);
        $this->assertNotNull($cashPayment->cash_receipt_number);
        $this->assertEquals($cashPayment->amount_payable, $cashPayment->amount_paid);

        // Test mobile money payment
        $mobilePayment = PaymentRecord::factory()
            ->mobileMoney()
            ->forConsultation($consultation)
            ->create();

        $this->assertEquals('Mobile Money', $mobilePayment->payment_method);
        $this->assertNotNull($mobilePayment->payment_phone_number);
        $this->assertStringStartsWith('MM', $mobilePayment->payment_reference);

        // Test card payment
        $cardPayment = PaymentRecord::factory()
            ->card()
            ->forConsultation($consultation)
            ->create();

        $this->assertEquals('Card', $cardPayment->payment_method);
        $this->assertNotNull($cardPayment->card_number);
        $this->assertNotNull($cardPayment->card_type);

        // Test partial payment
        $partialPayment = PaymentRecord::factory()
            ->partial()
            ->forConsultation($consultation)
            ->create();

        $this->assertLessThan($partialPayment->amount_payable, $partialPayment->amount_paid);
        $this->assertGreaterThan(0, $partialPayment->balance);

        // Test overpayment
        $overpayment = PaymentRecord::factory()
            ->overpaid()
            ->forConsultation($consultation)
            ->create();

        $this->assertGreaterThan($overpayment->amount_payable, $overpayment->amount_paid);
        $this->assertLessThan(0, $overpayment->balance);
    }

    /** @test */
    public function it_can_create_complete_medical_workflow()
    {
        // Create medical staff
        $patient = User::factory()->patient()->withMedicalData()->forEnterprise($this->enterprise->id)->create();
        $doctor = User::factory()->doctor()->forEnterprise($this->enterprise->id)->create();
        $nurse = User::factory()->nurse()->forEnterprise($this->enterprise->id)->create();
        $receptionist = User::factory()->receptionist()->forEnterprise($this->enterprise->id)->create();
        $cashier = User::factory()->cashier()->forEnterprise($this->enterprise->id)->create();

        // Create consultation
        $consultation = Consultation::factory()
            ->withComprehensiveData()
            ->forEnterprise($this->enterprise)
            ->forPatient($patient)
            ->create([
                'receptionist_id' => $receptionist->id
            ]);

        // Create medical services
        $labService = MedicalService::factory()
            ->laboratory()
            ->completed()
            ->forConsultation($consultation)
            ->assignedTo($nurse)
            ->create();

        $doctorService = MedicalService::factory()
            ->specialist()
            ->completed()
            ->withDocumentation()
            ->forConsultation($consultation)
            ->assignedTo($doctor)
            ->create();

        // Create billing items
        $consultationBill = BillingItem::factory()
            ->consultation()
            ->forConsultation($consultation)
            ->create();

        $labBill = BillingItem::factory()
            ->laboratory()
            ->forConsultation($consultation)
            ->create();

        // Create payment
        $payment = PaymentRecord::factory()
            ->cash()
            ->successful()
            ->forConsultation($consultation)
            ->create([
                'cash_received_by_id' => $cashier->id,
                'created_by_id' => $receptionist->id,
                'amount_payable' => $consultationBill->price + $labBill->price
            ]);

        // Verify relationships
        $this->assertEquals($patient->id, $consultation->patient_id);
        $this->assertEquals($consultation->id, $labService->consultation_id);
        $this->assertEquals($consultation->id, $doctorService->consultation_id);
        $this->assertEquals($consultation->id, $consultationBill->consultation_id);
        $this->assertEquals($consultation->id, $labBill->consultation_id);
        $this->assertEquals($consultation->id, $payment->consultation_id);

        // Verify medical workflow
        $this->assertEquals('completed', $labService->status);
        $this->assertEquals('completed', $doctorService->status);
        $this->assertEquals('Success', $payment->payment_status);
        $this->assertNotNull($patient->medical_history);
        $this->assertNotNull($consultation->diagnosis);
    }

    /**
     * Helper method to assert a value is between two numbers
     */
    private function assertBetween($actual, $min, $max)
    {
        $this->assertGreaterThanOrEqual($min, $actual);
        $this->assertLessThanOrEqual($max, $actual);
    }
}
