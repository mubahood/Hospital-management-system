<?php

namespace App\Admin\Widgets;

use App\Models\PaymentRecord;
use App\Models\Consultation;
use App\Models\BillingItem;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class FinancialOverview extends Widget
{
    protected $view = 'admin.widgets.financial-overview';

    /**
     * Get financial statistics
     */
    public function data()
    {
        $user = Admin::user();
        $enterpriseId = $user->enterprise_id;
        
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        // Total revenue this month
        $monthlyRevenue = PaymentRecord::where('enterprise_id', $enterpriseId)
            ->where('payment_status', 'Paid')
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        // Total revenue this week
        $weeklyRevenue = PaymentRecord::where('enterprise_id', $enterpriseId)
            ->where('payment_status', 'Paid')
            ->where('created_at', '>=', $weekStart)
            ->sum('amount');

        // Today's revenue
        $dailyRevenue = PaymentRecord::where('enterprise_id', $enterpriseId)
            ->where('payment_status', 'Paid')
            ->whereDate('created_at', $today)
            ->sum('amount');

        // Pending payments (outstanding bills)
        $pendingPayments = Consultation::where('enterprise_id', $enterpriseId)
            ->where('total_due', '>', 0)
            ->sum('total_due');

        // Total pending invoices count
        $pendingInvoicesCount = Consultation::where('enterprise_id', $enterpriseId)
            ->where('total_due', '>', 0)
            ->count();

        // Average consultation cost
        $avgConsultationCost = Consultation::where('enterprise_id', $enterpriseId)
            ->where('total_cost', '>', 0)
            ->avg('total_cost');

        // Total consultations this month (for revenue per consultation)
        $monthlyConsultations = Consultation::where('enterprise_id', $enterpriseId)
            ->where('created_at', '>=', $monthStart)
            ->count();

        // Calculate revenue per consultation
        $revenuePerConsultation = $monthlyConsultations > 0 ? $monthlyRevenue / $monthlyConsultations : 0;

        return [
            'monthly_revenue' => number_format($monthlyRevenue, 0),
            'weekly_revenue' => number_format($weeklyRevenue, 0),
            'daily_revenue' => number_format($dailyRevenue, 0),
            'pending_payments' => number_format($pendingPayments, 0),
            'pending_invoices_count' => $pendingInvoicesCount,
            'avg_consultation_cost' => number_format($avgConsultationCost, 0),
            'revenue_per_consultation' => number_format($revenuePerConsultation, 0),
            'monthly_consultations' => $monthlyConsultations,
            'month_name' => $monthStart->format('F'),
            'week_start' => $weekStart->format('M d'),
            'today_date' => $today->format('M d')
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
