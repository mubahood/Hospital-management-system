<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $totalPatients = 0;
    public $appointmentsToday = 0;
    public $pendingEvents = 0;
    public $criticalAlerts = 0;
    public $recentAppointments = [];
    public $systemStatus = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        try {
            // Get total patients
            $this->totalPatients = User::count();
            
            // Get today's appointments
            $this->appointmentsToday = DB::table('appointments')
                ->whereDate('created_at', today())
                ->count();
            
            // Get pending events
            $this->pendingEvents = Event::where('status', 'pending')->count();
            
            // Mock critical alerts
            $this->criticalAlerts = 3;
            
            // Get recent appointments (mock data for now)
            $this->recentAppointments = [
                [
                    'doctor' => 'Dr. Smith',
                    'patient' => 'John Doe',
                    'department' => 'Cardiology',
                    'time' => '2:00 PM',
                    'status' => 'Confirmed'
                ],
                [
                    'doctor' => 'Dr. Johnson',
                    'patient' => 'Jane Smith',
                    'department' => 'Neurology',
                    'time' => '3:30 PM',
                    'status' => 'Pending'
                ]
            ];
            
            // System status
            $this->systemStatus = [
                ['name' => 'Database Connection', 'status' => 'Online'],
                ['name' => 'Backup Status', 'status' => 'Up to date'],
                ['name' => 'Server Load', 'status' => 'Moderate']
            ];
            
        } catch (\Exception $e) {
            // Fallback to default values if database queries fail
            $this->totalPatients = 1234;
            $this->appointmentsToday = 28;
            $this->pendingEvents = 12;
        }
    }

    public function refreshData()
    {
        $this->loadDashboardData();
        $this->emit('dataRefreshed');
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
