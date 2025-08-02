<?php

namespace App\Admin\Widgets;

use App\Models\Consultation;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Widget;

class AppointmentStats extends Widget
{
    protected $view = 'admin.widgets.appointment-stats';

    /**
     * Get appointment statistics
     */
    public function data()
    {
        $user = Admin::user();
        $enterpriseId = $user->enterprise_id;
        
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        // Base query for appointments
        $baseQuery = Consultation::where('enterprise_id', $enterpriseId)
            ->whereNotNull('appointment_date');

        // Today's appointments
        $todayAppointments = (clone $baseQuery)
            ->whereDate('appointment_date', $today)
            ->count();

        // Tomorrow's appointments
        $tomorrowAppointments = (clone $baseQuery)
            ->whereDate('appointment_date', $tomorrow)
            ->count();

        // This week's appointments
        $weekAppointments = (clone $baseQuery)
            ->whereBetween('appointment_date', [$weekStart, $weekEnd])
            ->count();

        // This month's appointments
        $monthAppointments = (clone $baseQuery)
            ->whereBetween('appointment_date', [$monthStart, $monthEnd])
            ->count();

        // Status breakdown for today
        $todayStatus = (clone $baseQuery)
            ->whereDate('appointment_date', $today)
            ->selectRaw('appointment_status, COUNT(*) as count')
            ->groupBy('appointment_status')
            ->pluck('count', 'appointment_status')
            ->toArray();

        // Priority breakdown for today
        $todayPriority = (clone $baseQuery)
            ->whereDate('appointment_date', $today)
            ->selectRaw('appointment_priority, COUNT(*) as count')
            ->groupBy('appointment_priority')
            ->pluck('count', 'appointment_priority')
            ->toArray();

        // Upcoming appointments (next 7 days)
        $upcomingAppointments = (clone $baseQuery)
            ->whereBetween('appointment_date', [Carbon::now(), Carbon::now()->addDays(7)])
            ->with(['doctor'])
            ->orderBy('appointment_date', 'asc')
            ->limit(5)
            ->get();

        // Recent completed appointments
        $recentCompleted = (clone $baseQuery)
            ->where('appointment_status', 'completed')
            ->with(['doctor'])
            ->orderBy('appointment_date', 'desc')
            ->limit(5)
            ->get();

        // Doctor appointment counts for today
        $todayDoctorStats = (clone $baseQuery)
            ->whereDate('appointment_date', $today)
            ->selectRaw('doctor_id, COUNT(*) as count')
            ->groupBy('doctor_id')
            ->with('doctor')
            ->get()
            ->map(function($item) {
                return [
                    'doctor_name' => $item->doctor ? $item->doctor->name : 'Unknown',
                    'count' => $item->count
                ];
            });

        return [
            'today' => $todayAppointments,
            'tomorrow' => $tomorrowAppointments,
            'week' => $weekAppointments,
            'month' => $monthAppointments,
            'today_status' => $todayStatus,
            'today_priority' => $todayPriority,
            'upcoming' => $upcomingAppointments,
            'recent_completed' => $recentCompleted,
            'today_doctors' => $todayDoctorStats
        ];
    }

    /**
     * Render the widget
     */
    public function render()
    {
        $data = $this->data();
        return view($this->view, compact('data'));
    }
}
