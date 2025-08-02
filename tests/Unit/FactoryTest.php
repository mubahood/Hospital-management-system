<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Factories\ConsultationFactory;
use Database\Factories\MedicalServiceFactory;
use Database\Factories\BillingItemFactory;
use Database\Factories\PaymentRecordFactory;
use Database\Factories\UserFactory;

class FactoryTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_can_create_consultation_data()
    {
        $consultation = ConsultationFactory::new()->definition();
        
        $this->assertIsArray($consultation);
        $this->assertArrayHasKey('patient_id', $consultation);
        $this->assertArrayHasKey('receptionist_id', $consultation);
        $this->assertArrayHasKey('main_status', $consultation);
        $this->assertContains($consultation['main_status'], ['Pending', 'Ongoing', 'Billing', 'Payment', 'Completed']);
    }

    /** @test */
    public function it_can_create_medical_service_data()
    {
        $medicalService = MedicalServiceFactory::new()->definition();
        
        $this->assertIsArray($medicalService);
        $this->assertArrayHasKey('description', $medicalService);
        $this->assertArrayHasKey('unit_price', $medicalService);
        $this->assertArrayHasKey('type', $medicalService);
        $this->assertContains($medicalService['type'], ['laboratory', 'radiology', 'pharmacy', 'physiotherapy', 'nursing', 'specialist', 'surgery', 'procedure', 'other']);
    }

    /** @test */
    public function it_can_create_billing_item_data()
    {
        $billingItem = BillingItemFactory::new()->definition();
        
        $this->assertIsArray($billingItem);
        $this->assertArrayHasKey('description', $billingItem);
        $this->assertArrayHasKey('price', $billingItem);
        $this->assertArrayHasKey('type', $billingItem);
        $this->assertContains($billingItem['type'], ['consultation', 'laboratory', 'radiology', 'pharmacy', 'procedure', 'surgery', 'admission', 'bed_charge', 'nursing', 'physiotherapy', 'specialist', 'emergency', 'ambulance', 'other']);
    }

    /** @test */
    public function it_can_create_payment_record_data()
    {
        $paymentRecord = PaymentRecordFactory::new()->definition();
        
        $this->assertIsArray($paymentRecord);
        $this->assertArrayHasKey('amount_payable', $paymentRecord);
        $this->assertArrayHasKey('payment_method', $paymentRecord);
        $this->assertArrayHasKey('payment_status', $paymentRecord);
        $this->assertContains($paymentRecord['payment_method'], ['Cash', 'Card', 'Mobile Money', 'Flutterwave', 'Bank Transfer']);
    }

    /** @test */
    public function it_can_create_user_data_with_medical_roles()
    {
        // Test patient role
        $patient = UserFactory::new()->patient()->make()->toArray();
        $this->assertEquals('Patient', $patient['role']);
        $this->assertEquals('patient', $patient['user_type']);
        
        // Test doctor role
        $doctor = UserFactory::new()->doctor()->make()->toArray();
        $this->assertEquals('Doctor', $doctor['role']);
        $this->assertEquals('doctor', $doctor['user_type']);
        $this->assertArrayHasKey('medical_license_number', $doctor);
        
        // Test nurse role
        $nurse = UserFactory::new()->nurse()->make()->toArray();
        $this->assertEquals('Nurse', $nurse['role']);
        $this->assertEquals('nurse', $nurse['user_type']);
    }

    /** @test */
    public function it_can_create_specialized_consultation_data()
    {
        // Test ongoing consultation
        $ongoing = ConsultationFactory::new()->ongoing()->make()->toArray();
        $this->assertEquals('Ongoing', $ongoing['main_status']);
        
        // Test completed consultation  
        $completed = ConsultationFactory::new()->completed()->make()->toArray();
        $this->assertEquals('Completed', $completed['main_status']);
        $this->assertEquals('Paid', $completed['payment_status']);
        $this->assertArrayHasKey('diagnosis', $completed);
        
        // Test emergency consultation
        $emergency = ConsultationFactory::new()->emergency()->make()->toArray();
        $this->assertEquals('Emergency', $emergency['priority']);
        $this->assertStringContainsString('Emergency:', $emergency['reason_for_consultation']);
    }

    public function it_can_create_service_specific_medical_services()
    {
        // Test laboratory service
        $laboratory = MedicalServiceFactory::new()->laboratory()->make()->toArray();
        $this->assertEquals('laboratory', $laboratory['type']);
        $this->assertStringContainsStringIgnoringCase('lab', $laboratory['description']);
        
        // Test radiology service
        $radiology = MedicalServiceFactory::new()->radiology()->make()->toArray();
        $this->assertEquals('radiology', $radiology['type']);
        
        // Test surgery service
        $surgery = MedicalServiceFactory::new()->surgery()->make()->toArray();
        $this->assertEquals('surgery', $surgery['type']);
    }

    public function it_can_create_payment_method_specific_records()
    {
        // Test cash payment
        $cash = PaymentRecordFactory::new()->cash()->make()->toArray();
        $this->assertEquals('Cash', $cash['payment_method']);
        $this->assertArrayHasKey('cash_receipt_number', $cash);
        
        // Test mobile money payment
        $mobileMoney = PaymentRecordFactory::new()->mobileMoney()->make()->toArray();
        $this->assertEquals('Mobile Money', $mobileMoney['payment_method']);
        
        // Test flutterwave payment
        $flutterwave = PaymentRecordFactory::new()->flutterwave()->make()->toArray();
        $this->assertEquals('Flutterwave', $flutterwave['payment_method']);
    }

    /** @test */
    public function it_validates_medical_data_consistency()
    {
        $consultation = ConsultationFactory::new()->definition();
        
        // Validate vital signs are realistic
        $this->assertGreaterThan(50, $consultation['blood_pressure_systolic']);
        $this->assertLessThan(250, $consultation['blood_pressure_systolic']);
        $this->assertGreaterThan(30, $consultation['blood_pressure_diastolic']);
        $this->assertLessThan(150, $consultation['blood_pressure_diastolic']);
        
        // Validate temperature is realistic (in Celsius)
        $this->assertGreaterThan(35, $consultation['temperature']);
        $this->assertLessThan(43, $consultation['temperature']);
        
        // Validate pulse rate is realistic
        $this->assertGreaterThan(40, $consultation['pulse_rate']);
        $this->assertLessThan(200, $consultation['pulse_rate']);
    }
}
