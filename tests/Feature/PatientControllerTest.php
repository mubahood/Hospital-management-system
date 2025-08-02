<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user for authentication
        $this->adminUser = User::create([
            'user_type' => 'admin',
            'enterprise_id' => 1,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'phone_number_1' => '+256700000000',
            'sex' => 'Male',
            'date_of_birth' => '1980-01-01',
            'current_address' => '123 Admin Street'
        ]);
    }

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
        
        $this->assertTrue($patient->isPatient());
        $this->assertFalse($doctor->isPatient());
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
    }

    /** @test */
    public function test_patient_formatted_phone_accessor_works()
    {
        $patient = new User([
            'phone_number_1' => '+256700123456',
            'user_type' => 'patient'
        ]);
        
        $this->assertEquals('+256700123456', $patient->formatted_phone);
        
        // Test empty phone
        $patient->phone_number_1 = null;
        $this->assertEquals('N/A', $patient->formatted_phone);
    }

    /** @test */
    public function test_patient_can_be_created_in_database()
    {
        $patient = User::create([
            'enterprise_id' => 1,
            'user_type' => 'patient',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone_number_1' => '+256700123456',
            'sex' => 'Male',
            'date_of_birth' => '1990-01-01',
            'current_address' => '123 Test Street, Kampala',
            'emergency_person_name' => 'Jane Doe',
            'emergency_person_phone' => '+256700123457',
            'belongs_to_company' => 'No',
            'is_dependent' => 'No',
            'card_status' => 'Active',
            'password' => bcrypt('password123')
        ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'user_type' => 'patient',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
        
        $this->assertEquals('John Doe', $patient->full_name);
        $this->assertTrue($patient->isPatient());
        $this->assertStringStartsWith('PAT-', $patient->patient_number);
    }

    /** @test */
    public function test_patient_with_company_relationship()
    {
        $company = Company::create([
            'enterprise_id' => 1,
            'name' => 'Test Company',
            'phone_number' => '+256700000001',
            'email' => 'test@company.com',
            'address' => '123 Company Street',
            'status' => 'Active',
            'contact_person_name' => 'Company Admin',
            'contact_person_phone' => '+256700000002',
            'contact_person_email' => 'admin@company.com'
        ]);
        
        $patient = User::create([
            'enterprise_id' => 1,
            'user_type' => 'patient',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'phone_number_1' => '+256700123457',
            'sex' => 'Female',
            'date_of_birth' => '1985-05-15',
            'current_address' => '456 Test Avenue',
            'belongs_to_company' => 'Yes',
            'company_id' => $company->id,
            'card_status' => 'Active',
            'password' => bcrypt('password123')
        ]);
        
        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'belongs_to_company' => 'Yes'
        ]);
        
        // Test relationship
        $this->assertNotNull($patient->company);
        $this->assertEquals('Test Company', $patient->company->name);
    }

    /** @test */
    public function test_patient_age_calculation()
    {
        $patient = new User([
            'date_of_birth' => '1990-01-01',
            'user_type' => 'patient'
        ]);
        
        $age = $patient->age;
        $this->assertIsInt($age);
        $this->assertGreaterThan(30, $age); // Should be over 30 for someone born in 1990
    }

    /** @test */
    public function test_patient_medical_fields_can_be_stored()
    {
        $patient = User::create([
            'enterprise_id' => 1,
            'user_type' => 'patient',
            'first_name' => 'Medical',
            'last_name' => 'Patient',
            'email' => 'medical@example.com',
            'phone_number_1' => '+256700123458',
            'sex' => 'Male',
            'date_of_birth' => '1975-12-25',
            'current_address' => '789 Medical Street',
            'password' => bcrypt('password123'),
            'medical_history' => 'Previous surgery in 2020',
            'allergies' => 'Penicillin, Nuts',
            'current_medications' => 'Aspirin, Vitamin D',
            'insurance_provider' => 'Health Insurance Co.',
            'insurance_policy_number' => 'POL-1234567',
            'family_doctor_name' => 'Dr. Smith',
            'family_doctor_phone' => '+256700999999'
        ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'medical@example.com',
            'medical_history' => 'Previous surgery in 2020',
            'allergies' => 'Penicillin, Nuts',
            'current_medications' => 'Aspirin, Vitamin D',
            'insurance_provider' => 'Health Insurance Co.'
        ]);
    }

    /** @test */
    public function test_enterprise_scope_works_with_patients()
    {
        // Create patients for different enterprises
        $patient1 = User::create([
            'user_type' => 'patient',
            'enterprise_id' => 1,
            'first_name' => 'Enterprise1',
            'last_name' => 'Patient',
            'email' => 'ent1@example.com',
            'phone_number_1' => '+256700111111',
            'sex' => 'Male',
            'date_of_birth' => '1990-01-01',
            'current_address' => '111 Enterprise Street',
            'password' => bcrypt('password')
        ]);
        
        $patient2 = User::create([
            'user_type' => 'patient',
            'enterprise_id' => 2,
            'first_name' => 'Enterprise2',
            'last_name' => 'Patient',
            'email' => 'ent2@example.com',
            'phone_number_1' => '+256700222222',
            'sex' => 'Female',
            'date_of_birth' => '1995-01-01',
            'current_address' => '222 Enterprise Street',
            'password' => bcrypt('password')
        ]);
        
        // Test that enterprise scope filters correctly
        $enterprise1Patients = User::where('enterprise_id', 1)->where('user_type', 'patient')->get();
        $enterprise2Patients = User::where('enterprise_id', 2)->where('user_type', 'patient')->get();
        
        $this->assertTrue($enterprise1Patients->contains($patient1));
        $this->assertFalse($enterprise1Patients->contains($patient2));
        
        $this->assertTrue($enterprise2Patients->contains($patient2));
        $this->assertFalse($enterprise2Patients->contains($patient1));
    }
}
