<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run()
    {
        // Get first company or create one
        $company = Company::first();
        if (!$company) {
            $company = Company::create([
                'name' => 'Test Hospital',
                'type' => 'hospital',
                'email' => 'admin@hospital.com',
                'phone' => '123-456-7890',
                'address' => '123 Main St',
                'city' => 'Test City',
                'state' => 'TS',
                'zip' => '12345',
                'country' => 'USA'
            ]);
        }

        // Create sample events
        $events = [
            [
                'title' => 'Medical Conference 2024',
                'description' => 'Annual medical conference focusing on latest healthcare innovations and treatments.',
                'event_date' => now()->addDays(30),
                'event_time' => '09:00:00',
                'location' => 'Main Conference Hall',
                'max_attendees' => 500,
                'registration_fee' => 150.00,
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'title' => 'Emergency Response Training',
                'description' => 'Mandatory training for all emergency response staff.',
                'event_date' => now()->addDays(15),
                'event_time' => '14:00:00',
                'location' => 'Training Room A',
                'max_attendees' => 50,
                'registration_fee' => 0.00,
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'title' => 'Patient Care Workshop',
                'description' => 'Interactive workshop on improving patient care and satisfaction.',
                'event_date' => now()->addDays(45),
                'event_time' => '10:30:00',
                'location' => 'Auditorium B',
                'max_attendees' => 200,
                'registration_fee' => 75.00,
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'title' => 'Health Technology Summit',
                'description' => 'Exploring the future of health technology and digital healthcare solutions.',
                'event_date' => now()->addDays(60),
                'event_time' => '08:30:00',
                'location' => 'Innovation Center',
                'max_attendees' => 300,
                'registration_fee' => 200.00,
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'title' => 'Nursing Excellence Awards',
                'description' => 'Annual ceremony to recognize outstanding nursing professionals.',
                'event_date' => now()->addDays(90),
                'event_time' => '18:00:00',
                'location' => 'Grand Ballroom',
                'max_attendees' => 1000,
                'registration_fee' => 50.00,
                'is_active' => true,
                'company_id' => $company->id,
            ]
        ];

        foreach ($events as $event) {
            Event::create($event);
        }

        $this->command->info('Event seeder completed successfully!');
    }
}
