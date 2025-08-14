<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ComprehensiveDummyContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        
        $this->command->info('🏥 Starting Comprehensive Hospital Management System Dummy Content Generation...');
        
        // Step 1: Create Enterprise Owner
        $this->command->info('1️⃣ Creating Enterprise Owner...');
        $enterpriseOwner = $this->createEnterpriseOwner($faker);
        
        // Step 2: Create New Hospital Enterprise
        $this->command->info('2️⃣ Creating Hospital Enterprise...');
        $hospital = $this->createHospitalEnterprise($faker, $enterpriseOwner);
        
        // Step 3: Setup Hospital Infrastructure
        $this->command->info('3️⃣ Setting up Hospital Infrastructure...');
        $departments = $this->createHospitalDepartments($faker, $hospital);
        $staff = $this->createHospitalStaff($faker, $hospital, $departments);
        $companies = $this->createCompanies($faker, $hospital);
        
        // Step 4: Setup Inventory System
        $this->command->info('4️⃣ Setting up Inventory System...');
        $stockCategories = $this->createStockCategories($faker, $hospital);
        $stockItems = $this->createStockItems($faker, $hospital, $stockCategories);
        $this->createStockOuts($faker, $hospital, $stockItems);
        
        // Step 5: Create Medical Services
        $this->command->info('5️⃣ Creating Medical Services...');
        $medicalServices = $this->createMedicalServices($faker, $hospital);
        
        // Step 6: Patient Journey Testing
        $this->command->info('6️⃣ Testing Complete Patient Journey...');
        $patients = $this->createPatients($faker, $hospital);
        $this->testConsultationJourney($faker, $hospital, $patients, $staff, $medicalServices);
        
        $this->command->info('✅ Comprehensive Hospital Management System Dummy Content Generation Completed!');
    }
    
    /**
     * Create enterprise owner
     */
    private function createEnterpriseOwner($faker)
    {
        $enterpriseOwnerRole = Role::firstOrCreate(['name' => 'enterprise_owner']);
        
        $enterpriseOwner = User::create([
            'first_name' => 'Dr. Sarah',
            'last_name' => 'Thompson',
            'name' => 'Dr. Sarah Thompson',
            'email' => 'sarah.thompson@greenvallyhospital.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'phone_number' => '+1-555-0123',
            'address' => '123 Medical Center Drive, Healthcare City, HC 12345',
            'date_of_birth' => $faker->dateTimeBetween('-60 years', '-30 years')->format('Y-m-d'),
            'gender' => 'Female',
            'is_patient' => false,
            'is_active' => true,
            'enterprise_id' => null, // Will be assigned after enterprise creation
        ]);
        
        $enterpriseOwner->assignRole($enterpriseOwnerRole);
        
        $this->command->info("✓ Enterprise Owner created: {$enterpriseOwner->name} ({$enterpriseOwner->email})");
        
        return $enterpriseOwner;
    }
    
    /**
     * Create hospital enterprise
     */
    private function createHospitalEnterprise($faker, $enterpriseOwner)
    {
        $hospital = Enterprise::create([
            'name' => 'Green Valley Medical Center',
            'email' => 'admin@greenvallyhospital.com',
            'phone' => '+1-555-0100',
            'address' => '456 Healthcare Boulevard, Medical District, MD 54321',
            'business_reg_number' => 'HMC-' . $faker->numerify('####'),
            'administrator_id' => $enterpriseOwner->id,
            'details' => 'A state-of-the-art medical facility providing comprehensive healthcare services including emergency care, surgery, diagnostics, and specialized treatments.',
            'website' => 'https://www.greenvallyhospital.com',
        ]);
        
        // Update enterprise owner's enterprise
        $enterpriseOwner->update(['enterprise_id' => $hospital->id]);
        
        $this->command->info("✓ Hospital Enterprise created: {$hospital->name}");
        
        return $hospital;
    }
    
    /**
     * Create hospital departments
     */
    private function createHospitalDepartments($faker, $hospital)
    {
        $departments = [
            [
                'name' => 'Emergency Department',
                'description' => '24/7 emergency medical care and trauma services',
                'phone' => '+1-555-0101',
                'email' => 'emergency@greenvallyhospital.com'
            ],
            [
                'name' => 'Internal Medicine',
                'description' => 'Comprehensive adult medical care and chronic disease management',
                'phone' => '+1-555-0102',
                'email' => 'internal@greenvallyhospital.com'
            ],
            [
                'name' => 'Pediatrics',
                'description' => 'Specialized medical care for infants, children, and adolescents',
                'phone' => '+1-555-0103',
                'email' => 'pediatrics@greenvallyhospital.com'
            ],
            [
                'name' => 'Cardiology',
                'description' => 'Heart and cardiovascular system diagnosis and treatment',
                'phone' => '+1-555-0104',
                'email' => 'cardiology@greenvallyhospital.com'
            ],
            [
                'name' => 'Orthopedics',
                'description' => 'Bone, joint, muscle, and ligament care and surgery',
                'phone' => '+1-555-0105',
                'email' => 'orthopedics@greenvallyhospital.com'
            ],
            [
                'name' => 'Radiology',
                'description' => 'Medical imaging and diagnostic services',
                'phone' => '+1-555-0106',
                'email' => 'radiology@greenvallyhospital.com'
            ],
            [
                'name' => 'Laboratory',
                'description' => 'Clinical laboratory testing and pathology services',
                'phone' => '+1-555-0107',
                'email' => 'lab@greenvallyhospital.com'
            ],
            [
                'name' => 'Pharmacy',
                'description' => 'Medication dispensing and pharmaceutical care',
                'phone' => '+1-555-0108',
                'email' => 'pharmacy@greenvallyhospital.com'
            ]
        ];
        
        $createdDepartments = [];
        foreach ($departments as $dept) {
            $department = Department::create([
                'name' => $dept['name'],
                'description' => $dept['description'],
                'phone_number' => $dept['phone'],
                'email' => $dept['email'],
                'enterprise_id' => $hospital->id,
                'is_active' => true,
            ]);
            
            $createdDepartments[] = $department;
            $this->command->info("  ✓ Department created: {$department->name}");
        }
        
        return $createdDepartments;
    }
    
    /**
     * Create hospital staff
     */
    private function createHospitalStaff($faker, $hospital, $departments)
    {
        // Ensure medical roles exist
        $roles = [
            'doctor' => Role::firstOrCreate(['name' => 'doctor']),
            'nurse' => Role::firstOrCreate(['name' => 'nurse']),
            'technician' => Role::firstOrCreate(['name' => 'technician']),
            'pharmacist' => Role::firstOrCreate(['name' => 'pharmacist']),
            'receptionist' => Role::firstOrCreate(['name' => 'receptionist']),
            'admin' => Role::firstOrCreate(['name' => 'admin']),
        ];
        
        $staff = [];
        
        // Create doctors for each department
        foreach ($departments as $department) {
            for ($i = 1; $i <= 2; $i++) {
                $gender = $faker->randomElement(['Male', 'Female']);
                $firstName = $faker->firstName($gender === 'Male' ? 'male' : 'female');
                $lastName = $faker->lastName;
                
                $doctor = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => "Dr. {$firstName} {$lastName}",
                    'email' => strtolower($firstName . '.' . $lastName) . '@greenvallyhospital.com',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'phone_number' => $faker->phoneNumber,
                    'address' => $faker->address,
                    'date_of_birth' => $faker->dateTimeBetween('-65 years', '-28 years')->format('Y-m-d'),
                    'gender' => $gender,
                    'is_patient' => false,
                    'is_active' => true,
                    'enterprise_id' => $hospital->id,
                    'department_id' => $department->id,
                    'license_number' => 'MD-' . $faker->numerify('######'),
                    'specialization' => $department->name,
                ]);
                
                $doctor->assignRole($roles['doctor']);
                $staff[] = $doctor;
                
                $this->command->info("    ✓ Doctor created: {$doctor->name} - {$department->name}");
            }
        }
        
        // Create nurses
        for ($i = 1; $i <= 8; $i++) {
            $gender = $faker->randomElement(['Male', 'Female']);
            $firstName = $faker->firstName($gender === 'Male' ? 'male' : 'female');
            $lastName = $faker->lastName;
            
            $nurse = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => "{$firstName} {$lastName}",
                'email' => strtolower($firstName . '.' . $lastName . '.nurse') . '@greenvallyhospital.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
                'date_of_birth' => $faker->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
                'gender' => $gender,
                'is_patient' => false,
                'is_active' => true,
                'enterprise_id' => $hospital->id,
                'department_id' => $departments[array_rand($departments)]->id,
                'license_number' => 'RN-' . $faker->numerify('######'),
            ]);
            
            $nurse->assignRole($roles['nurse']);
            $staff[] = $nurse;
            
            $this->command->info("    ✓ Nurse created: {$nurse->name}");
        }
        
        // Create support staff
        $supportStaff = [
            ['role' => 'technician', 'count' => 4, 'prefix' => 'TECH'],
            ['role' => 'pharmacist', 'count' => 2, 'prefix' => 'PHARM'],
            ['role' => 'receptionist', 'count' => 3, 'prefix' => 'REC'],
        ];
        
        foreach ($supportStaff as $staffType) {
            for ($i = 1; $i <= $staffType['count']; $i++) {
                $gender = $faker->randomElement(['Male', 'Female']);
                $firstName = $faker->firstName($gender === 'Male' ? 'male' : 'female');
                $lastName = $faker->lastName;
                
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => "{$firstName} {$lastName}",
                    'email' => strtolower($firstName . '.' . $lastName . '.' . $staffType['role']) . '@greenvallyhospital.com',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'phone_number' => $faker->phoneNumber,
                    'address' => $faker->address,
                    'date_of_birth' => $faker->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
                    'gender' => $gender,
                    'is_patient' => false,
                    'is_active' => true,
                    'enterprise_id' => $hospital->id,
                    'department_id' => $departments[array_rand($departments)]->id,
                    'license_number' => $staffType['prefix'] . '-' . $faker->numerify('######'),
                ]);
                
                $user->assignRole($roles[$staffType['role']]);
                $staff[] = $user;
                
                $this->command->info("    ✓ {$staffType['role']} created: {$user->name}");
            }
        }
        
        return $staff;
    }
    
    /**
     * Create companies (insurance/partners)
     */
    private function createCompanies($faker, $hospital)
    {
        $companies = [];
        $companyData = [
            [
                'name' => 'HealthFirst Insurance',
                'short_name' => 'HealthFirst',
                'details' => 'Leading health insurance provider with 80% coverage'
            ],
            [
                'name' => 'MediCare Plus',
                'short_name' => 'MediCare+',
                'details' => 'Comprehensive medical insurance with 75% coverage'
            ],
            [
                'name' => 'Universal Health Coverage',
                'short_name' => 'UHC',
                'details' => 'Universal health insurance with 90% coverage'
            ],
            [
                'name' => 'Prime Medical Insurance',
                'short_name' => 'PrimeMed',
                'details' => 'Premium medical insurance with 85% coverage'
            ],
        ];
        
        foreach ($companyData as $comp) {
            $company = Company::create([
                'name' => $comp['name'],
                'short_name' => $comp['short_name'],
                'details' => $comp['details'],
                'phone_number' => $faker->phoneNumber,
                'email' => strtolower(str_replace(' ', '', $comp['name'])) . '@insurance.com',
                'address' => $faker->address,
                'enterprise_id' => $hospital->id,
                'administrator_id' => 1, // Default admin
            ]);
            
            $companies[] = $company;
            $this->command->info("  ✓ Company created: " . $company->name);
        }
        
        return $companies;
    }
    
    /**
     * Create stock categories
     */
    private function createStockCategories($faker, $hospital)
    {
        $categories = [
            'Medications',
            'Medical Supplies',
            'Surgical Instruments',
            'Diagnostic Equipment',
            'Office Supplies',
            'Personal Protective Equipment',
            'Laboratory Reagents',
            'Cleaning Supplies'
        ];
        
        $stockCategories = [];
        foreach ($categories as $categoryName) {
            $category = StockItemCategory::create([
                'name' => $categoryName,
                'description' => "Category for {$categoryName}",
                'enterprise_id' => $hospital->id,
            ]);
            
            $stockCategories[] = $category;
            $this->command->info("  ✓ Stock Category created: {$category->name}");
        }
        
        return $stockCategories;
    }
    
    /**
     * Create stock items
     */
    private function createStockItems($faker, $hospital, $stockCategories)
    {
        $stockItems = [];
        
        $items = [
            ['name' => 'Amoxicillin 500mg', 'category' => 'Medications', 'unit' => 'Tablets', 'price' => 0.50],
            ['name' => 'Paracetamol 500mg', 'category' => 'Medications', 'unit' => 'Tablets', 'price' => 0.25],
            ['name' => 'Insulin Pen', 'category' => 'Medications', 'unit' => 'Units', 'price' => 25.00],
            ['name' => 'Surgical Gloves', 'category' => 'Medical Supplies', 'unit' => 'Pairs', 'price' => 0.75],
            ['name' => 'Disposable Syringes', 'category' => 'Medical Supplies', 'unit' => 'Pieces', 'price' => 0.30],
            ['name' => 'Bandages', 'category' => 'Medical Supplies', 'unit' => 'Rolls', 'price' => 2.50],
            ['name' => 'Surgical Scissors', 'category' => 'Surgical Instruments', 'unit' => 'Pieces', 'price' => 45.00],
            ['name' => 'Scalpel Blades', 'category' => 'Surgical Instruments', 'unit' => 'Pieces', 'price' => 1.25],
            ['name' => 'Blood Pressure Monitor', 'category' => 'Diagnostic Equipment', 'unit' => 'Units', 'price' => 150.00],
            ['name' => 'Thermometer', 'category' => 'Diagnostic Equipment', 'unit' => 'Units', 'price' => 25.00],
        ];
        
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
            $this->command->info("    ✓ Stock Item created: {$stockItem->name}");
        }
        
        return $stockItems;
    }
    
    /**
     * Create stock outs
     */
    private function createStockOuts($faker, $hospital, $stockItems)
    {
        foreach ($stockItems as $stockItem) {
            if ($faker->boolean(30)) { // 30% chance of stock out
                $stockOut = StockOutRecord::create([
                    'stock_item_id' => $stockItem->id,
                    'quantity_out' => $faker->numberBetween(10, 100),
                    'reason' => $faker->randomElement([
                        'Patient Treatment',
                        'Emergency Use',
                        'Routine Procedure',
                        'Surgery',
                        'Laboratory Test'
                    ]),
                    'issued_to' => $faker->name,
                    'issued_by' => $faker->name,
                    'date_issued' => $faker->dateTimeBetween('-30 days', 'now'),
                    'enterprise_id' => $hospital->id,
                ]);
                
                // Update stock level
                $stockItem->current_stock_level -= $stockOut->quantity_out;
                $stockItem->save();
                
                $this->command->info("    ✓ Stock Out created: " . $stockOut->quantity_out . " " . $stockItem->name);
            }
        }
    }
    
    /**
     * Create medical services
     */
    private function createMedicalServices($faker, $hospital)
    {
        $services = [
            ['name' => 'General Consultation', 'price' => 50.00, 'duration' => 30],
            ['name' => 'Specialist Consultation', 'price' => 100.00, 'duration' => 45],
            ['name' => 'Blood Test', 'price' => 25.00, 'duration' => 15],
            ['name' => 'X-Ray', 'price' => 75.00, 'duration' => 20],
            ['name' => 'MRI Scan', 'price' => 500.00, 'duration' => 60],
            ['name' => 'CT Scan', 'price' => 300.00, 'duration' => 30],
            ['name' => 'Ultrasound', 'price' => 80.00, 'duration' => 25],
            ['name' => 'ECG', 'price' => 40.00, 'duration' => 15],
            ['name' => 'Minor Surgery', 'price' => 800.00, 'duration' => 90],
            ['name' => 'Physical Therapy', 'price' => 60.00, 'duration' => 45],
        ];
        
        $medicalServices = [];
        foreach ($services as $service) {
            $medicalService = MedicalServiceItem::create([
                'name' => $service['name'],
                'description' => "Professional {$service['name']} service",
                'price' => $service['price'],
                'duration_minutes' => $service['duration'],
                'enterprise_id' => $hospital->id,
                'is_active' => true,
            ]);
            
            $medicalServices[] = $medicalService;
            $this->command->info("  ✓ Medical Service created: " . $medicalService->name . " - $" . $medicalService->price);
        }
        
        return $medicalServices;
    }
    
    /**
     * Create patients
     */
    private function createPatients($faker, $hospital)
    {
        $patientRole = Role::firstOrCreate(['name' => 'patient']);
        
        $patients = [];
        for ($i = 1; $i <= 20; $i++) {
            $gender = $faker->randomElement(['Male', 'Female']);
            $firstName = $faker->firstName($gender === 'Male' ? 'male' : 'female');
            $lastName = $faker->lastName;
            
            $patient = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => "{$firstName} {$lastName}",
                'email' => strtolower($firstName . '.' . $lastName . '.patient') . '@example.com',
                'password' => Hash::make('password123'),
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
                'date_of_birth' => $faker->dateTimeBetween('-80 years', '-1 years')->format('Y-m-d'),
                'gender' => $gender,
                'is_patient' => true,
                'is_active' => true,
                'enterprise_id' => $hospital->id,
                
                // Patient-specific fields
                'emergency_contact_name' => $faker->name,
                'emergency_contact_phone' => $faker->phoneNumber,
                'medical_history' => $faker->randomElement([
                    'No known allergies. History of hypertension.',
                    'Allergic to penicillin. Diabetic.',
                    'No significant medical history.',
                    'History of heart disease. Taking blood thinners.',
                    'Asthmatic. Allergic to shellfish.',
                ]),
                'current_medications' => $faker->randomElement([
                    'Metformin 500mg twice daily',
                    'Lisinopril 10mg once daily',
                    'No current medications',
                    'Insulin as prescribed',
                    'Albuterol inhaler as needed',
                ]),
                'allergies' => $faker->randomElement([
                    'No known allergies',
                    'Penicillin',
                    'Shellfish',
                    'Latex',
                    'Peanuts',
                ]),
                'blood_type' => $faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
                'height_cm' => $faker->numberBetween(150, 200),
                'weight_kg' => $faker->numberBetween(50, 120),
            ]);
            
            $patient->assignRole($patientRole);
            $patients[] = $patient;
            
            $this->command->info("  ✓ Patient created: {$patient->name} - {$patient->blood_type}");
        }
        
        return $patients;
    }
    
    /**
     * Test complete consultation journey
     */
    private function testConsultationJourney($faker, $hospital, $patients, $staff, $medicalServices)
    {
        $doctors = collect($staff)->filter(function ($user) {
            return $user->hasRole('doctor');
        });
        
        foreach ($patients as $index => $patient) {
            if ($index >= 10) break; // Test with first 10 patients
            
            $doctor = $doctors->random();
            
            // Create consultation
            $consultation = Consultation::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'department_id' => $doctor->department_id,
                'chief_complaint' => $faker->randomElement([
                    'Chest pain and shortness of breath',
                    'Persistent cough and fever',
                    'Abdominal pain and nausea',
                    'Headache and dizziness',
                    'Back pain and muscle stiffness',
                    'Skin rash and itching',
                    'Joint pain and swelling',
                    'Fatigue and weakness',
                ]),
                'history_of_present_illness' => $faker->paragraph,
                'physical_examination' => $faker->paragraph,
                'vital_signs' => json_encode([
                    'blood_pressure' => $faker->numberBetween(90, 180) . '/' . $faker->numberBetween(60, 120),
                    'heart_rate' => $faker->numberBetween(60, 100),
                    'temperature' => $faker->randomFloat(1, 96.5, 102.0),
                    'respiratory_rate' => $faker->numberBetween(12, 20),
                    'oxygen_saturation' => $faker->numberBetween(95, 100),
                ]),
                'diagnosis' => $faker->randomElement([
                    'Hypertension',
                    'Upper Respiratory Infection',
                    'Gastroenteritis',
                    'Migraine',
                    'Muscle Strain',
                    'Contact Dermatitis',
                    'Arthritis',
                    'Viral Syndrome',
                ]),
                'treatment_plan' => $faker->paragraph,
                'scheduled_date' => $faker->dateTimeBetween('-7 days', '+7 days'),
                'status' => 'scheduled',
                'enterprise_id' => $hospital->id,
                'consultation_fee' => $faker->randomFloat(2, 50, 200),
                'total_amount' => 0, // Will be calculated
            ]);
            
            $this->command->info("    ✓ Consultation created: {$patient->name} -> {$doctor->name}");
            
            // Add medical services to consultation
            $selectedServices = $medicalServices->random($faker->numberBetween(1, 4));
            $totalAmount = $consultation->consultation_fee;
            
            foreach ($selectedServices as $serviceItem) {
                $service = MedicalService::create([
                    'consultation_id' => $consultation->id,
                    'medical_service_item_id' => $serviceItem->id,
                    'quantity' => 1,
                    'unit_price' => $serviceItem->price,
                    'total_price' => $serviceItem->price,
                    'status' => $faker->randomElement(['pending', 'in_progress', 'completed']),
                    'notes' => $faker->sentence,
                    'performed_by' => $doctor->id,
                    'performed_at' => $faker->dateTimeBetween('-7 days', 'now'),
                    'enterprise_id' => $hospital->id,
                ]);
                
                $totalAmount += $service->total_price;
                $this->command->info("      ✓ Service added: " . $serviceItem->name . " - $" . $service->total_price);
            }
            
            // Update consultation total
            $consultation->update(['total_amount' => $totalAmount]);
            
            // Progress consultation through stages for some patients
            if ($index < 5) {
                // Complete all services
                MedicalService::where('consultation_id', $consultation->id)
                    ->update(['status' => 'completed']);
                
                // Move to billing
                $consultation->update(['status' => 'billing']);
                $this->command->info("      ✓ Consultation moved to billing");
                
                // Move to payment
                $consultation->update(['status' => 'payment']);
                $this->command->info("      ✓ Consultation moved to payment");
                
                // Create payment records
                $remainingAmount = $totalAmount;
                $paymentCount = $faker->numberBetween(1, 3);
                
                for ($p = 0; $p < $paymentCount && $remainingAmount > 0; $p++) {
                    $paymentAmount = $p === $paymentCount - 1 ? 
                        $remainingAmount : 
                        $faker->randomFloat(2, 10, $remainingAmount * 0.8);
                    
                    $payment = PaymentRecord::create([
                        'consultation_id' => $consultation->id,
                        'amount' => $paymentAmount,
                        'payment_method' => $faker->randomElement(['cash', 'card', 'insurance', 'bank_transfer']),
                        'payment_date' => $faker->dateTimeBetween('-7 days', 'now'),
                        'reference_number' => 'PAY-' . $faker->numerify('######'),
                        'notes' => $faker->sentence,
                        'enterprise_id' => $hospital->id,
                    ]);
                    
                    $remainingAmount -= $paymentAmount;
                    $this->command->info("        ✓ Payment recorded: $" . $payment->amount . " via " . $payment->payment_method);
                }
                
                // If fully paid, mark as completed
                if ($remainingAmount <= 0.01) {
                    $consultation->update(['status' => 'completed']);
                    $this->command->info("      ✓ Consultation completed - Fully paid");
                }
            }
        }
    }
}
