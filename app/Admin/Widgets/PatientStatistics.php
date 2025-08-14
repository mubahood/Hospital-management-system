<?php

namespace App\Admin\Widgets;

use App\Models\Patient;
use App\Models\Consultation;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Widget;

class PatientStatistics extends Widget
{
    protected $view = 'admin.widgets.patient-statistics';

    /**
     * Get patient statistics
     */
    public function data()
    {
        $user = Admin::user();
        $enterpriseId = $user->enterprise_id;
        
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        // Total patients in the system
        $totalPatients = User::where('enterprise_id', $enterpriseId)
            ->where('user_type', 'patient')
            ->count();

        // New patients this week
        $newPatientsWeek = User::where('enterprise_id', $enterpriseId)
            ->where('user_type', 'patient')
            ->where('created_at', '>=', $weekStart)
            ->count();

        // New patients this month
        $newPatientsMonth = User::where('enterprise_id', $enterpriseId)
            ->where('user_type', 'patient')
            ->where('created_at', '>=', $monthStart)
            ->count();

        // Active consultations (ongoing treatment)
        $activeCases = Consultation::where('enterprise_id', $enterpriseId)
            ->where('main_status', 'Ongoing')
            ->count();

        // Completed consultations this month
        $completedMonth = Consultation::where('enterprise_id', $enterpriseId)
            ->where('main_status', 'Completed')
            ->where('created_at', '>=', $monthStart)
            ->count();

        // Today's consultations
        $todayConsultations = Consultation::where('enterprise_id', $enterpriseId)
            ->whereDate('created_at', $today)
            ->count();

        // Patients with pending follow-ups
        $pendingFollowUps = Consultation::where('enterprise_id', $enterpriseId)
            ->where('main_status', 'Pending')
            ->whereNotNull('appointment_date')
            ->where('appointment_date', '>=', $today)
            ->count();

        return [
            'total_patients' => $totalPatients,
            'new_patients_week' => $newPatientsWeek,
            'new_patients_month' => $newPatientsMonth,
            'active_cases' => $activeCases,
            'completed_month' => $completedMonth,
            'today_consultations' => $todayConsultations,
            'pending_followups' => $pendingFollowUps,
            'week_start' => $weekStart->format('M d'),
            'month_name' => $monthStart->format('F'),
            'today_date' => $today->format('M d, Y')
        ];
    }

    /**
     * Render the widget
     */
    public function render()
    {
        return view($this->view, [
            'data' => $this->data()
        ])->render();
    }
}
