<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create test mobile users for Enterprise ID 1
        $users = [
            [
                'username' => 'mobileuser1',
                'email' => 'mobile1@hospital.com',
                'password' => Hash::make('password123'),
                'name' => 'Mobile User One',
                'first_name' => 'Mobile',
                'last_name' => 'User One',
                'phone_number_1' => '+256700000001',
                'enterprise_id' => 1,
                'user_type' => 'mobile_user',
                'status' => 2, // 2 = Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'mobileuser2',
                'email' => 'mobile2@hospital.com',
                'password' => Hash::make('password123'),
                'name' => 'Mobile User Two',
                'first_name' => 'Mobile',
                'last_name' => 'User Two',
                'phone_number_1' => '+256700000002',
                'enterprise_id' => 1,
                'user_type' => 'mobile_user',
                'status' => 2, // 2 = Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'doctor1',
                'email' => 'doctor1@hospital.com',
                'password' => Hash::make('password123'),
                'name' => 'Dr. John Smith',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'phone_number_1' => '+256700000003',
                'enterprise_id' => 1,
                'user_type' => 'doctor',
                'status' => 2, // 2 = Active
                'title' => 'Doctor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'nurse1',
                'email' => 'nurse1@hospital.com',
                'password' => Hash::make('password123'),
                'name' => 'Jane Doe',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'phone_number_1' => '+256700000004',
                'enterprise_id' => 1,
                'user_type' => 'nurse',
                'status' => 2, // 2 = Active
                'title' => 'Nurse',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Mobile test users created successfully!');
    }
}
