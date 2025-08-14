<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ComprehensiveUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        
        $this->command->info('👥 Creating 50 comprehensive users with all fields...');
        
        // User types for variety
        $userTypes = ['patient', 'doctor', 'nurse', 'administrator', 'pharmacist', 'technician'];
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $maritalStatuses = ['single', 'married', 'divorced', 'widowed', 'separated'];
        $cardStatuses = ['active', 'inactive', 'suspended', 'expired'];
        $employmentStatuses = ['employed', 'unemployed', 'retired', 'student', 'self-employed'];
        $educationLevels = ['Primary', 'Secondary', 'Certificate', 'Diploma', 'Bachelor', 'Master', 'PhD'];
        $religions = ['Christian', 'Muslim', 'Hindu', 'Buddhist', 'Jewish', 'Other'];
        $languages = ['English', 'Swahili', 'Luganda', 'French', 'Arabic'];
        $nationalities = ['Ugandan', 'Kenyan', 'Tanzanian', 'Rwandan', 'American', 'British', 'Canadian'];
        
        for ($i = 1; $i <= 50; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $email = strtolower($firstName . '.' . $lastName . '@' . $faker->safeEmailDomain);
            $phoneNumber = '+256' . $faker->numerify('7########');
            $userType = $faker->randomElement($userTypes);
            $sex = $faker->randomElement(['male', 'female']);
            $birthDate = $faker->dateTimeBetween('-80 years', '-18 years');
            $cardNumber = $faker->numerify('HOS-####-####');
            
            $userData = [
                'enterprise_id' => 1, // Default enterprise
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
                'email_verified_at' => $faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
                'password' => Hash::make('password123'),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $email,
                'phone_number_1' => $phoneNumber,
                'phone_number_2' => $faker->optional(0.3)->phoneNumber,
                'date_of_birth' => $birthDate->format('Y-m-d'),
                'place_of_birth' => $faker->city . ', ' . $faker->country,
                'sex' => $sex,
                'home_address' => $faker->address,
                'current_address' => $faker->optional(0.8)->address,
                'nationality' => $faker->randomElement($nationalities),
                'religion' => $faker->randomElement($religions),
                'spouse_name' => $faker->optional(0.4)->name,
                'spouse_phone' => $faker->optional(0.4)->phoneNumber,
                'father_name' => $faker->name('male'),
                'father_phone' => $faker->optional(0.6)->phoneNumber,
                'mother_name' => $faker->name('female'),
                'mother_phone' => $faker->optional(0.6)->phoneNumber,
                'languages' => implode(', ', $faker->randomElements($languages, $faker->numberBetween(1, 3))),
                'emergency_person_name' => $faker->name,
                'emergency_person_phone' => $faker->phoneNumber,
                'emergency_contact_relationship' => $faker->randomElement(['spouse', 'parent', 'sibling', 'friend', 'colleague']),
                'national_id_number' => 'NIN' . $faker->numerify('##########'),
                'passport_number' => $faker->optional(0.3)->bothify('??######'),
                'tin' => $faker->optional(0.5)->numerify('##########'),
                'nssf_number' => $faker->optional(0.4)->numerify('NSSF#######'),
                'bank_name' => $faker->optional(0.6)->randomElement(['Stanbic Bank', 'Centenary Bank', 'DFCU Bank', 'Barclays Bank', 'Standard Chartered']),
                'bank_account_number' => $faker->optional(0.6)->numerify('##########'),
                'marital_status' => $faker->randomElement($maritalStatuses),
                'title' => $this->getTitle($userType, $sex),
                'company_id' => $faker->optional(0.3)->numberBetween(1, 5),
                'user_type' => $userType,
                'patient_status' => $userType === 'patient' ? $faker->randomElement(['active', 'inactive', 'discharged']) : null,
                'avatar' => null,
                'intro' => $faker->optional(0.5)->paragraph,
                'rate' => $userType === 'doctor' ? $faker->randomFloat(2, 50, 500) : 0,
                'belongs_to_company' => $faker->optional(0.2)->company,
                'card_status' => $faker->randomElement($cardStatuses),
                'card_number' => $cardNumber,
                'card_balance' => $faker->randomFloat(2, 0, 10000),
                'card_accepts_credit' => $faker->boolean(30),
                'card_max_credit' => $faker->randomFloat(2, 500, 5000),
                'card_accepts_cash' => $faker->boolean(90),
                'is_dependent' => $faker->boolean(20),
                'dependent_status' => $faker->optional(0.2)->randomElement(['child', 'spouse', 'parent']),
                'dependent_id' => $faker->optional(0.2)->numberBetween(1, 10),
                'card_expiry' => $faker->dateTimeBetween('now', '+5 years')->format('Y-m-d'),
                'belongs_to_company_status' => $faker->optional(0.3)->randomElement(['active', 'inactive']),
                
                // Medical fields
                'medical_history' => $this->generateMedicalHistory($faker),
                'allergies' => $faker->optional(0.4)->randomElements(['Penicillin', 'Peanuts', 'Shellfish', 'Latex', 'Pollen'], $faker->numberBetween(0, 2)),
                'current_medications' => $faker->optional(0.3)->sentence,
                'blood_type' => $faker->randomElement($bloodTypes),
                'height' => $faker->randomFloat(2, 150, 200), // cm
                'weight' => $faker->randomFloat(2, 45, 120), // kg
                'insurance_provider' => $faker->optional(0.5)->randomElement(['AAR Healthcare', 'Resolution Insurance', 'UAP Insurance', 'NHIF']),
                'insurance_policy_number' => $faker->optional(0.5)->numerify('INS#######'),
                'insurance_expiry_date' => $faker->optional(0.5)->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
                'family_doctor_name' => $faker->optional(0.4)->name,
                'family_doctor_phone' => $faker->optional(0.4)->phoneNumber,
                'employment_status' => $faker->randomElement($employmentStatuses),
                'employer_name' => $faker->optional(0.7)->company,
                'annual_income' => $faker->optional(0.6)->randomFloat(2, 1200000, 50000000), // UGX
                'education_level' => $faker->randomElement($educationLevels),
                'preferred_language' => $faker->randomElement($languages),
                'remember_token' => null,
                'created_at' => now()->subDays($faker->numberBetween(1, 365)),
                'updated_at' => now(),
            ];
            
            // Convert arrays to strings where needed
            if (is_array($userData['allergies'])) {
                $userData['allergies'] = implode(', ', $userData['allergies']);
            }
            
            DB::table('admin_users')->insert($userData);
            
            if ($i % 10 == 0) {
                $this->command->info("  ✓ Created $i users...");
            }
        }
        
        $this->command->info('✅ Successfully created 50 comprehensive users with all fields populated!');
        $this->command->info('📊 User types distribution:');
        
        // Show distribution
        $counts = DB::table('admin_users')
            ->select('user_type', DB::raw('count(*) as count'))
            ->groupBy('user_type')
            ->get();
            
        foreach ($counts as $count) {
            $this->command->info("  - {$count->user_type}: {$count->count} users");
        }
    }
    
    /**
     * Get appropriate title based on user type and gender
     */
    private function getTitle($userType, $sex)
    {
        $titles = [
            'doctor' => $sex === 'male' ? 'Dr.' : 'Dr.',
            'nurse' => $sex === 'male' ? 'Mr.' : 'Ms.',
            'administrator' => $sex === 'male' ? 'Mr.' : 'Ms.',
            'patient' => $sex === 'male' ? 'Mr.' : ['Ms.', 'Mrs.'][rand(0, 1)],
            'pharmacist' => $sex === 'male' ? 'Mr.' : 'Ms.',
            'technician' => $sex === 'male' ? 'Mr.' : 'Ms.',
        ];
        
        return $titles[$userType] ?? ($sex === 'male' ? 'Mr.' : 'Ms.');
    }
    
    /**
     * Generate realistic medical history
     */
    private function generateMedicalHistory($faker)
    {
        $conditions = [
            'Hypertension', 'Diabetes Type 2', 'Asthma', 'Arthritis', 
            'Migraine', 'Depression', 'Anxiety', 'High Cholesterol',
            'Heart Disease', 'Kidney Disease', 'Previous Surgery',
            'Broken Bone', 'Chronic Back Pain'
        ];
        
        if ($faker->boolean(60)) {
            $selectedConditions = $faker->randomElements($conditions, $faker->numberBetween(1, 3));
            return implode(', ', $selectedConditions) . '. ' . $faker->sentence;
        }
        
        return 'No significant medical history.';
    }
}
