<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Enterprise;
use App\Models\Department;
use App\Models\Company;
use App\Models\StockItemCategory;
use App\Models\StockItem;
use App\Models\StockOutRecord;
use App\Models\Consultation;
use App\Models\MedicalService;
use App\Models\PaymentRecord;
use Faker\Factory as Faker;

class HospitalDummyContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        
        $this->command->info('🏥 Starting Hospital Management System Dummy Content Generation...');
        
        // Step 1: Create Enterprise Owner and Hospital
        $this->command->info('1️⃣ Creating Hospital and Owner...');
        $hospital = $this->createHospitalWithOwner($faker);
        
        // Step 2: Setup Hospital Infrastructure
        $this->command->info('2️⃣ Setting up Hospital Infrastructure...');
        $departments = $this->createDepartments($faker, $hospital);
        $staff = $this->createStaff($faker, $hospital, $departments);
        $companies = $this->createCompanies($faker, $hospital);
        
        // Step 3: Setup Inventory System
        $this->command->info('3️⃣ Setting up Inventory System...');
        $stockCategories = $this->createStockCategories($faker, $hospital);
        $stockItems = $this->createStockItems($faker, $hospital, $stockCategories);
        $this->createStockOuts($faker, $hospital, $stockItems);
        
        // Step 4: Create Patients and Test Consultation Journey
        $this->command->info('4️⃣ Creating Patients and Testing Consultation Journey...');
        $patients = $this->createPatients($faker, $hospital);
        $this->testConsultationJourney($faker, $hospital, $patients, $staff);
        
        $this->command->info('✅ Hospital Management System Dummy Content Generation Completed!');
    }
    
    private function createHospitalOwner($faker, $hospitalName)
    {
        // Create a hospital owner user directly in admin_users table
        // The admin_users table has limited columns: id, username, password, name, avatar, remember_token, created_at, updated_at
        $userId = DB::table('admin_users')->insertGetId([
            'username' => $faker->unique()->userName,
            'name' => $faker->name,
            'password' => Hash::make('password123'),
            'avatar' => null,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $userId;
    }
    
    private function createHospitalWithOwner($faker)
    {
        $hospitalName = 'Green Valley Medical Center';
        
        // First check if email column exists and add it if it doesn't
        try {
            DB::select("SELECT email FROM admin_users LIMIT 1");
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), "Unknown column 'email'")) {
                try {
                    DB::statement("ALTER TABLE admin_users ADD COLUMN email VARCHAR(255) NULL AFTER username");
                    $this->command->info('  ✓ Added email column to admin_users table');
                } catch (\Exception $altError) {
                    $this->command->warn('  ⚠ Could not add email column: ' . $altError->getMessage());
                }
            }
        }
        
        // Step 1: Create hospital owner with email
        $ownerId = DB::table('admin_users')->insertGetId([
            'username' => 'hospital.owner',
            'name' => 'Dr. Sarah Mitchell',
            'email' => 'owner@greenvalleymedical.com',
            'password' => Hash::make('password123'),
            'avatar' => null,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Step 2: Create the enterprise (hospital) using direct DB insert
        $hospitalId = DB::table('enterprises')->insertGetId([
            'type' => 'Hospital',
            'name' => $hospitalName,
            'short_name' => 'GVMC',
            'phone_number' => '+1-555-0123',
            'phone_number_2' => '+1-555-0124',
            'address' => '123 Medical Drive, Healthcare City, HC 12345',
            'services' => 'Emergency Care, Surgery, Diagnostics, Pharmacy',
            'details' => 'A comprehensive medical facility providing quality healthcare services to the community.',
            'logo' => 'default-hospital-logo.png',
            'website' => 'https://greenvalleymedical.com',
            'email' => 'info@greenvalleymedical.com',
            'color' => '#2563eb',
            'mission' => 'To provide compassionate, quality healthcare to our community',
            'vision' => 'To be the leading healthcare provider in the region',
            'years_of_operation' => 15,
            'license_number' => 'HOS-123456',
            'registration_number' => 'REG-789012',
            'instagram' => '@greenvalleymedical',
            'linkedin' => 'linkedin.com/company/greenvalleymedical',
            'twitter' => '@greenvalleymc',
            'facebook' => 'facebook.com/greenvalleymedical',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info("  ✓ Hospital: " . $hospitalName);
        $this->command->info("  ✓ Owner ID: " . $ownerId);
        
        return (object) [
            'id' => $hospitalId,
            'name' => $hospitalName,
            'owner_id' => $ownerId
        ];
    }
    
    private function createDepartments($faker, $hospital)
    {
        $departmentData = [
            ['name' => 'Emergency Department', 'description' => '24/7 emergency medical care'],
            ['name' => 'Internal Medicine', 'description' => 'Adult medical care and chronic disease management'],
            ['name' => 'Pediatrics', 'description' => 'Medical care for children and adolescents'],
            ['name' => 'Cardiology', 'description' => 'Heart and cardiovascular care'],
            ['name' => 'Orthopedics', 'description' => 'Bone, joint, and muscle care'],
            ['name' => 'Radiology', 'description' => 'Medical imaging services'],
            ['name' => 'Laboratory', 'description' => 'Clinical laboratory testing'],
            ['name' => 'Pharmacy', 'description' => 'Medication services'],
        ];
        
        $departments = [];
        foreach ($departmentData as $dept) {
            $departmentId = DB::table('departments')->insertGetId([
                'name' => $dept['name'],
                'description' => $dept['description'],
                'phone_number' => $faker->phoneNumber,
                'email' => strtolower(str_replace(' ', '', $dept['name'])) . '@greenvallyhospital.com',
                'enterprise_id' => $hospital->id,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $departments[] = (object) [
                'id' => $departmentId,
                'name' => $dept['name']
            ];
            $this->command->info("  ✓ Department: " . $dept['name']);
        }
        
        return $departments;
    }
    
    private function createStaff($faker, $hospital, $departments)
    {
        $staff = [];
        
        // Create doctors - using DB direct insert since admin_users table has limited columns
        foreach ($departments as $department) {
            for ($i = 1; $i <= 2; $i++) {
                $firstName = $faker->firstName;
                $lastName = $faker->lastName;
                
                $doctorId = DB::table('admin_users')->insertGetId([
                    'username' => strtolower($firstName . '.' . $lastName . '.dr'),
                    'name' => "Dr. {$firstName} {$lastName}",
                    'password' => Hash::make('password123'),
                    'avatar' => null,
                    'remember_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $staff[] = (object)['id' => $doctorId, 'name' => "Dr. {$firstName} {$lastName}", 'type' => 'doctor'];
                $this->command->info("    ✓ Doctor: Dr. {$firstName} {$lastName} - " . $department->name);
            }
        }
        
        // Create nurses - using DB direct insert since admin_users table has limited columns
        for ($i = 1; $i <= 8; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            
            $nurseId = DB::table('admin_users')->insertGetId([
                'username' => strtolower($firstName . '.' . $lastName . '.nurse'),
                'name' => "{$firstName} {$lastName}",
                'password' => Hash::make('password123'),
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $staff[] = (object)['id' => $nurseId, 'name' => "{$firstName} {$lastName}", 'type' => 'nurse'];
            $this->command->info("    ✓ Nurse: {$firstName} {$lastName}");
        }
        
        return $staff;
    }
    
    private function createCompanies($faker, $hospital)
    {
        $companies = [];
        $companyData = [
            ['name' => 'HealthFirst Insurance', 'short_name' => 'HealthFirst'],
            ['name' => 'MediCare Plus', 'short_name' => 'MediCare+'],
            ['name' => 'Universal Health Coverage', 'short_name' => 'UHC'],
            ['name' => 'Prime Medical Insurance', 'short_name' => 'PrimeMed'],
        ];
        
        foreach ($companyData as $comp) {
            $company = Company::create([
                'name' => $comp['name'],
                'short_name' => $comp['short_name'],
                'details' => 'Insurance partner providing coverage for patients',
                'phone_number' => $faker->phoneNumber,
                'email' => strtolower(str_replace(' ', '', $comp['name'])) . '@insurance.com',
                'address' => $faker->address,
                'enterprise_id' => $hospital->id,
                'administrator_id' => 1,
            ]);
            
            $companies[] = $company;
            $this->command->info("  ✓ Company: " . $company->name);
        }
        
        return $companies;
    }
    
    private function createStockCategories($faker, $hospital)
    {
        $categories = [
            'Medications', 'Medical Supplies', 'Surgical Instruments', 
            'Diagnostic Equipment', 'Office Supplies', 'PPE',
            'Laboratory Reagents', 'Cleaning Supplies'
        ];
        
        $stockCategories = [];
        foreach ($categories as $categoryName) {
            $category = StockItemCategory::create([
                'name' => $categoryName,
                'description' => "Category for {$categoryName}",
                'enterprise_id' => $hospital->id,
            ]);
            
            $stockCategories[] = $category;
            $this->command->info("  ✓ Stock Category: " . $category->name);
        }
        
        return $stockCategories;
    }
    
    private function createStockItems($faker, $hospital, $stockCategories)
    {
        $items = [
            ['name' => 'Amoxicillin 500mg', 'category' => 'Medications', 'unit' => 'Tablets', 'price' => 0.50],
            ['name' => 'Paracetamol 500mg', 'category' => 'Medications', 'unit' => 'Tablets', 'price' => 0.25],
            ['name' => 'Surgical Gloves', 'category' => 'Medical Supplies', 'unit' => 'Pairs', 'price' => 0.75],
            ['name' => 'Disposable Syringes', 'category' => 'Medical Supplies', 'unit' => 'Pieces', 'price' => 0.30],
            ['name' => 'Bandages', 'category' => 'Medical Supplies', 'unit' => 'Rolls', 'price' => 2.50],
            ['name' => 'Surgical Scissors', 'category' => 'Surgical Instruments', 'unit' => 'Pieces', 'price' => 45.00],
            ['name' => 'Blood Pressure Monitor', 'category' => 'Diagnostic Equipment', 'unit' => 'Units', 'price' => 150.00],
            ['name' => 'Thermometer', 'category' => 'Diagnostic Equipment', 'unit' => 'Units', 'price' => 25.00],
        ];
        
        $stockItems = [];
        foreach ($items as $item) {
            $category = collect($stockCategories)->firstWhere('name', $item['category']);
            
            $stockItem = StockItem::create([
                'name' => $item['name'],
                'description' => "Medical {$item['name']} for hospital use",
                'unit_of_measurement' => $item['unit'],
                'unit_price' => $item['price'],
                'current_stock_level' => $faker->numberBetween(50, 500),
                'minimum_stock_level' => $faker->numberBetween(10, 50),
                'maximum_stock_level' => $faker->numberBetween(500, 1000),
                'stock_item_category_id' => $category->id,
                'enterprise_id' => $hospital->id,
            ]);
            
            $stockItems[] = $stockItem;
            $this->command->info("    ✓ Stock Item: " . $stockItem->name);
        }
        
        return $stockItems;
    }
    
    private function createStockOuts($faker, $hospital, $stockItems)
    {
        foreach ($stockItems as $stockItem) {
            if ($faker->boolean(30)) {
                $stockOut = StockOutRecord::create([
                    'stock_item_id' => $stockItem->id,
                    'quantity_out' => $faker->numberBetween(10, 100),
                    'reason' => $faker->randomElement(['Patient Treatment', 'Emergency Use', 'Surgery']),
                    'issued_to' => $faker->name,
                    'issued_by' => $faker->name,
                    'date_issued' => $faker->dateTimeBetween('-30 days', 'now'),
                    'enterprise_id' => $hospital->id,
                ]);
                
                $stockItem->current_stock_level -= $stockOut->quantity_out;
                $stockItem->save();
                
                $this->command->info("    ✓ Stock Out: " . $stockOut->quantity_out . " " . $stockItem->name);
            }
        }
    }
    
    private function createPatients($faker, $hospital)
    {
        $patients = [];
        for ($i = 1; $i <= 15; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            
            $patientId = DB::table('admin_users')->insertGetId([
                'username' => strtolower($firstName . '.' . $lastName . '.patient'),
                'name' => "{$firstName} {$lastName}",
                'password' => Hash::make('password123'),
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $patients[] = (object)['id' => $patientId, 'name' => "{$firstName} {$lastName}", 'type' => 'patient'];
            $this->command->info("  ✓ Patient: {$firstName} {$lastName}");
        }
        
        return $patients;
    }
    
    private function testConsultationJourney($faker, $hospital, $patients, $staff)
    {
        $doctors = collect($staff)->filter(function ($user) {
            return $user->type === 'doctor';
        });
        
        foreach ($patients as $index => $patient) {
            if ($index >= 8) break; // Test with first 8 patients
            
            $doctor = $doctors->random();
            
            // Create consultation
            $consultation = Consultation::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'chief_complaint' => $faker->randomElement([
                    'Chest pain and shortness of breath',
                    'Persistent cough and fever',
                    'Abdominal pain and nausea',
                    'Headache and dizziness',
                    'Back pain and muscle stiffness',
                ]),
                'history_of_present_illness' => $faker->paragraph,
                'physical_examination' => $faker->paragraph,
                'vital_signs' => json_encode([
                    'blood_pressure' => $faker->numberBetween(90, 180) . '/' . $faker->numberBetween(60, 120),
                    'heart_rate' => $faker->numberBetween(60, 100),
                    'temperature' => $faker->randomFloat(1, 96.5, 102.0),
                ]),
                'diagnosis' => $faker->randomElement([
                    'Hypertension', 'Upper Respiratory Infection', 'Gastroenteritis',
                    'Migraine', 'Muscle Strain', 'Viral Syndrome',
                ]),
                'treatment_plan' => $faker->paragraph,
                'scheduled_date' => $faker->dateTimeBetween('-7 days', '+7 days'),
                'status' => 'scheduled',
                'enterprise_id' => $hospital->id,
                'consultation_fee' => $faker->randomFloat(2, 50, 200),
                'total_amount' => 0,
            ]);
            
            $this->command->info("    ✓ Consultation: " . $patient->name . " -> " . $doctor->name);
            
            // Add medical services
            $serviceCount = $faker->numberBetween(1, 3);
            $totalAmount = $consultation->consultation_fee;
            
            for ($s = 1; $s <= $serviceCount; $s++) {
                $servicePrice = $faker->randomFloat(2, 25, 300);
                
                $service = MedicalService::create([
                    'consultation_id' => $consultation->id,
                    'patient_id' => $patient->id,
                    'assigned_to_id' => $doctor->id,
                    'type' => $faker->randomElement(['Blood Test', 'X-Ray', 'Ultrasound', 'ECG']),
                    'description' => $faker->sentence,
                    'quantity' => 1,
                    'unit_price' => $servicePrice,
                    'total_price' => $servicePrice,
                    'status' => $faker->randomElement(['pending', 'in_progress', 'completed']),
                    'enterprise_id' => $hospital->id,
                ]);
                
                $totalAmount += $service->total_price;
                $this->command->info("      ✓ Service: " . $service->type . " - $" . $service->total_price);
            }
            
            // Update consultation total
            $consultation->update(['total_amount' => $totalAmount]);
            
            // Progress consultation for some patients
            if ($index < 4) {
                // Complete services and move through billing process
                MedicalService::where('consultation_id', $consultation->id)
                    ->update(['status' => 'completed']);
                
                $consultation->update(['status' => 'billing']);
                $this->command->info("      ✓ Moved to billing");
                
                $consultation->update(['status' => 'payment']);
                $this->command->info("      ✓ Moved to payment");
                
                // Create payment
                $payment = PaymentRecord::create([
                    'consultation_id' => $consultation->id,
                    'amount_payable' => $totalAmount,
                    'amount_paid' => $totalAmount,
                    'balance' => 0,
                    'payment_method' => $faker->randomElement(['cash', 'card', 'insurance']),
                    'payment_date' => now()->format('Y-m-d'),
                    'payment_status' => 'completed',
                    'enterprise_id' => $hospital->id,
                ]);
                
                $consultation->update(['status' => 'completed']);
                $this->command->info("      ✓ Payment completed: $" . $payment->amount_paid);
                $this->command->info("      ✓ Consultation completed");
            }
        }
    }
}
