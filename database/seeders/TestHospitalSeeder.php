<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Enterprise;
use Faker\Factory as Faker;

class TestHospitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        
        $this->command->info('🏥 Testing Hospital Creation...');
        
        // Step 1: Create hospital owner
        $this->command->info('1️⃣ Creating Hospital Owner...');
        $ownerId = DB::table('admin_users')->insertGetId([
            'username' => 'hospital_owner_test',
            'name' => 'Test Hospital Owner',
            'password' => Hash::make('password123'),
            'avatar' => null,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info("  ✓ Owner created with ID: " . $ownerId);
        
        // Step 2: Create the enterprise (hospital)
        $this->command->info('2️⃣ Creating Hospital Enterprise...');
        $hospital = Enterprise::create([
            'name' => 'Test Medical Center',
            'phone_number' => $faker->phoneNumber,
            'address' => $faker->address,
            'details' => 'A test medical facility.',
            'logo' => null,
            'color' => '#2563eb',
            'website' => 'https://testmedical.com',
            'email' => 'info@testmedical.com',
            'administrator_id' => $ownerId,
            'motto' => 'Test Motto',
        ]);
        
        $this->command->info("  ✓ Hospital created: " . $hospital->name);
        $this->command->info('✅ Test completed successfully!');
    }
}
