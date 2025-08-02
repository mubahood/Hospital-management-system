<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class PatientModelTest extends TestCase
{
    /** @test */
    public function test_patient_number_is_auto_generated()
    {
        $patient = new User([
            'enterprise_id' => 1,
            'user_type' => 'patient',
            'first_name' => 'Test',
            'last_name' => 'Patient'
        ]);
        
        $this->assertNotEmpty($patient->patient_number);
        $this->assertStringStartsWith('PAT-', $patient->patient_number);
    }

    /** @test */
    public function test_patient_is_patient_method_works()
    {
        $patient = new User(['user_type' => 'patient']);
        $doctor = new User(['user_type' => 'doctor']);
        $nurse = new User(['user_type' => 'nurse']);
        $admin = new User(['user_type' => 'admin']);
        
        $this->assertTrue($patient->isPatient());
        $this->assertFalse($doctor->isPatient());
        $this->assertFalse($nurse->isPatient());
        $this->assertFalse($admin->isPatient());
    }

    /** @test */
    public function test_patient_full_name_accessor_works()
    {
        $patient = new User([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'user_type' => 'patient'
        ]);
        
        $this->assertEquals('John Doe', $patient->full_name);
        
        // Test with empty last name
        $patient->last_name = '';
        $this->assertEquals('John', $patient->full_name);
        
        // Test with empty first name
        $patient->first_name = '';
        $patient->last_name = 'Doe';
        $this->assertEquals('Doe', $patient->full_name);
    }

    /** @test */
    public function test_patient_formatted_phone_accessor_works()
    {
        $patient = new User([
            'phone_number_1' => '+256700123456',
            'user_type' => 'patient'
        ]);
        
        $this->assertEquals('+256700123456', $patient->formatted_phone);
        
        // Test with empty phone
        $patient->phone_number_1 = null;
        $this->assertEquals('N/A', $patient->formatted_phone);
        
        // Test with empty string
        $patient->phone_number_1 = '';
        $this->assertEquals('N/A', $patient->formatted_phone);
    }

    /** @test */
    public function test_patient_age_calculation()
    {
        // Test exact age calculation
        $patient = new User([
            'date_of_birth' => '1990-01-01',
            'user_type' => 'patient'
        ]);
        
        $age = $patient->age;
        $this->assertIsInt($age);
        $this->assertGreaterThanOrEqual(30, $age); // Should be at least 30 for someone born in 1990
        
        // Test with more recent date
        $patient->date_of_birth = '2000-01-01';
        $age = $patient->age;
        $this->assertGreaterThanOrEqual(20, $age); // Should be at least 20 for someone born in 2000
    }

    /** @test */
    public function test_patient_medical_fields_are_accessible()
    {
        $patient = new User([
            'user_type' => 'patient',
            'medical_history' => 'Previous surgery in 2020',
            'allergies' => 'Penicillin, Nuts',
            'current_medications' => 'Aspirin, Vitamin D',
            'insurance_provider' => 'Health Insurance Co.',
            'insurance_policy_number' => 'POL-1234567',
            'family_doctor_name' => 'Dr. Smith',
            'family_doctor_phone' => '+256700999999'
        ]);
        
        $this->assertEquals('Previous surgery in 2020', $patient->medical_history);
        $this->assertEquals('Penicillin, Nuts', $patient->allergies);
        $this->assertEquals('Aspirin, Vitamin D', $patient->current_medications);
        $this->assertEquals('Health Insurance Co.', $patient->insurance_provider);
        $this->assertEquals('POL-1234567', $patient->insurance_policy_number);
        $this->assertEquals('Dr. Smith', $patient->family_doctor_name);
        $this->assertEquals('+256700999999', $patient->family_doctor_phone);
    }

    /** @test */
    public function test_patient_company_fields_are_accessible()
    {
        $patient = new User([
            'user_type' => 'patient',
            'belongs_to_company' => 'Yes',
            'company_id' => 123,
            'is_dependent' => 'No',
            'card_status' => 'Active'
        ]);
        
        $this->assertEquals('Yes', $patient->belongs_to_company);
        $this->assertEquals(123, $patient->company_id);
        $this->assertEquals('No', $patient->is_dependent);
        $this->assertEquals('Active', $patient->card_status);
    }

    /** @test */
    public function test_patient_employment_fields_are_accessible()
    {
        $patient = new User([
            'user_type' => 'patient',
            'employment_status' => 'Employed',
            'employer_name' => 'Test Company Ltd',
            'annual_income' => 25000000,
            'education_level' => 'Bachelor'
        ]);
        
        $this->assertEquals('Employed', $patient->employment_status);
        $this->assertEquals('Test Company Ltd', $patient->employer_name);
        $this->assertEquals(25000000, $patient->annual_income);
        $this->assertEquals('Bachelor', $patient->education_level);
    }

    /** @test */
    public function test_patient_enterprise_scope_logic()
    {
        $patient1 = new User([
            'user_type' => 'patient',
            'enterprise_id' => 1,
            'first_name' => 'Patient1'
        ]);
        
        $patient2 = new User([
            'user_type' => 'patient',
            'enterprise_id' => 2,
            'first_name' => 'Patient2'
        ]);
        
        // Test that patients have different enterprise IDs
        $this->assertEquals(1, $patient1->enterprise_id);
        $this->assertEquals(2, $patient2->enterprise_id);
        $this->assertNotEquals($patient1->enterprise_id, $patient2->enterprise_id);
    }

    /** @test */
    public function test_patient_emergency_contact_fields()
    {
        $patient = new User([
            'user_type' => 'patient',
            'emergency_person_name' => 'John Emergency',
            'emergency_person_phone' => '+256700888888'
        ]);
        
        $this->assertEquals('John Emergency', $patient->emergency_person_name);
        $this->assertEquals('+256700888888', $patient->emergency_person_phone);
    }
}
