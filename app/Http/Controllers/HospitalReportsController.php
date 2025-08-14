<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class HospitalReportsController extends Controller
{
    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        $reportSummary = [
            'total_patients' => User::count(),
            'appointments_today' => $this->getAppointmentsToday(),
            'revenue_this_month' => $this->getRevenueThisMonth(),
            'active_treatments' => $this->getActiveTreatments(),
        ];

        return view('admin.reports.dashboard', compact('reportSummary'));
    }

    /**
     * Patient Treatment History Reports
     */
    public function patientTreatmentHistory(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $patientId = $request->get('patient_id');

        $query = DB::table('admin_users as patients')
            ->leftJoin('consultations', 'patients.id', '=', 'consultations.patient_id')
            ->leftJoin('treatment_records', 'patients.id', '=', 'treatment_records.patient_id')
            ->select([
                'patients.id as patient_id',
                'patients.name as patient_name',
                'patients.phone as patient_phone',
                'patients.email as patient_email',
                'patients.date_of_birth',
                'patients.sex as gender',
                'consultations.id as consultation_id',
                'consultations.type as consultation_type',
                'consultations.status as consultation_status',
                'consultations.symptoms',
                'consultations.diagnosis',
                'consultations.treatment_plan',
                'consultations.created_at as consultation_date',
                'treatment_records.id as treatment_id',
                'treatment_records.treatment_type',
                'treatment_records.medication_prescribed',
                'treatment_records.created_at as treatment_date'
            ])
            ->whereBetween('consultations.created_at', [$dateFrom, $dateTo])
            ->orWhereBetween('treatment_records.created_at', [$dateFrom, $dateTo]);

        if ($patientId) {
            $query->where('patients.id', $patientId);
        }

        $treatments = $query->orderBy('consultations.created_at', 'desc')->get();

        $patients = User::select('id', 'name')->get();

        return view('admin.reports.patient-treatment-history', compact(
            'treatments', 'dateFrom', 'dateTo', 'patientId', 'patients'
        ));
    }

    /**
     * Financial Reporting Dashboard
     */
    public function financialReports(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // Revenue Analytics
        $revenueData = [
            'total_revenue' => $this->getTotalRevenue($dateFrom, $dateTo),
            'consultation_revenue' => $this->getConsultationRevenue($dateFrom, $dateTo),
            'pharmacy_revenue' => $this->getPharmacyRevenue($dateFrom, $dateTo),
            'pending_payments' => $this->getPendingPayments($dateFrom, $dateTo),
            'daily_revenue' => $this->getDailyRevenue($dateFrom, $dateTo),
        ];

        // Payment Methods Analysis
        $paymentMethods = $this->getPaymentMethodsAnalysis($dateFrom, $dateTo);

        // Top Services by Revenue
        $topServices = $this->getTopServicesByRevenue($dateFrom, $dateTo);

        return view('admin.reports.financial-dashboard', compact(
            'revenueData', 'paymentMethods', 'topServices', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Inventory Management Reports
     */
    public function inventoryReports(Request $request)
    {
        $lowStockThreshold = $request->get('threshold', 10);

        $inventoryData = [
            'low_stock_items' => $this->getLowStockItems($lowStockThreshold),
            'expiring_medications' => $this->getExpiringMedications(),
            'stock_movement' => $this->getStockMovement(),
            'inventory_value' => $this->getInventoryValue(),
            'top_consumed_items' => $this->getTopConsumedItems(),
        ];

        return view('admin.reports.inventory-management', compact('inventoryData', 'lowStockThreshold'));
    }

    /**
     * Appointment Scheduling Analytics
     */
    public function appointmentAnalytics(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $appointmentData = [
            'total_appointments' => $this->getTotalAppointments($dateFrom, $dateTo),
            'appointments_by_status' => $this->getAppointmentsByStatus($dateFrom, $dateTo),
            'appointments_by_doctor' => $this->getAppointmentsByDoctor($dateFrom, $dateTo),
            'appointment_trends' => $this->getAppointmentTrends($dateFrom, $dateTo),
            'no_show_rate' => $this->getNoShowRate($dateFrom, $dateTo),
            'average_wait_time' => $this->getAverageWaitTime($dateFrom, $dateTo),
        ];

        return view('admin.reports.appointment-analytics', compact('appointmentData', 'dateFrom', 'dateTo'));
    }

    // Helper Methods for Data Retrieval

    private function getAppointmentsToday()
    {
        return DB::table('appointments')
            ->whereDate('appointment_date', Carbon::today())
            ->count();
    }

    private function getRevenueThisMonth()
    {
        return DB::table('billing_items')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');
    }

    private function getActiveTreatments()
    {
        return DB::table('treatment_records')
            ->where('status', 'active')
            ->count();
    }

    private function getTotalRevenue($dateFrom, $dateTo)
    {
        return DB::table('billing_items')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('amount');
    }

    private function getConsultationRevenue($dateFrom, $dateTo)
    {
        return DB::table('consultations')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('amount');
    }

    private function getPharmacyRevenue($dateFrom, $dateTo)
    {
        return DB::table('stock_items')
            ->join('stock_out_records', 'stock_items.id', '=', 'stock_out_records.stock_item_id')
            ->whereBetween('stock_out_records.created_at', [$dateFrom, $dateTo])
            ->sum(DB::raw('stock_out_records.quantity_out * stock_items.selling_price'));
    }

    private function getPendingPayments($dateFrom, $dateTo)
    {
        return DB::table('billing_items')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('payment_status', 'pending')
            ->sum('amount');
    }

    private function getDailyRevenue($dateFrom, $dateTo)
    {
        return DB::table('billing_items')
            ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getPaymentMethodsAnalysis($dateFrom, $dateTo)
    {
        return DB::table('payment_records')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('payment_method')
            ->get();
    }

    private function getTopServicesByRevenue($dateFrom, $dateTo)
    {
        return DB::table('consultations')
            ->selectRaw('type as service, COUNT(*) as count, SUM(amount) as revenue')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('type')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->get();
    }

    private function getLowStockItems($threshold)
    {
        return DB::table('stock_items')
            ->where('current_quantity', '<=', $threshold)
            ->select('id', 'name', 'current_quantity', 'unit_of_measure', 'selling_price')
            ->get();
    }

    private function getExpiringMedications()
    {
        $expiryDate = Carbon::now()->addDays(30);
        return DB::table('stock_items')
            ->where('expiry_date', '<=', $expiryDate)
            ->where('expiry_date', '>', Carbon::now())
            ->select('id', 'name', 'current_quantity', 'expiry_date', 'category_id')
            ->get();
    }

    private function getStockMovement()
    {
        return DB::table('stock_out_records')
            ->join('stock_items', 'stock_out_records.stock_item_id', '=', 'stock_items.id')
            ->selectRaw('stock_items.name, SUM(quantity_out) as total_out')
            ->whereMonth('stock_out_records.created_at', Carbon::now()->month)
            ->groupBy('stock_items.id', 'stock_items.name')
            ->orderBy('total_out', 'desc')
            ->limit(10)
            ->get();
    }

    private function getInventoryValue()
    {
        return DB::table('stock_items')
            ->selectRaw('SUM(current_quantity * purchase_price) as total_value')
            ->first()->total_value ?? 0;
    }

    private function getTopConsumedItems()
    {
        return DB::table('stock_out_records')
            ->join('stock_items', 'stock_out_records.stock_item_id', '=', 'stock_items.id')
            ->selectRaw('stock_items.name, SUM(quantity_out) as total_consumed')
            ->whereMonth('stock_out_records.created_at', Carbon::now()->month)
            ->groupBy('stock_items.id', 'stock_items.name')
            ->orderBy('total_consumed', 'desc')
            ->limit(10)
            ->get();
    }

    private function getTotalAppointments($dateFrom, $dateTo)
    {
        return DB::table('appointments')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->count();
    }

    private function getAppointmentsByStatus($dateFrom, $dateTo)
    {
        return DB::table('appointments')
            ->selectRaw('status, COUNT(*) as count')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->groupBy('status')
            ->get();
    }

    private function getAppointmentsByDoctor($dateFrom, $dateTo)
    {
        return DB::table('appointments')
            ->join('admin_users', 'appointments.doctor_id', '=', 'admin_users.id')
            ->selectRaw('admin_users.name as doctor_name, COUNT(*) as appointment_count')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->groupBy('appointments.doctor_id', 'admin_users.name')
            ->orderBy('appointment_count', 'desc')
            ->get();
    }

    private function getAppointmentTrends($dateFrom, $dateTo)
    {
        return DB::table('appointments')
            ->selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getNoShowRate($dateFrom, $dateTo)
    {
        $totalAppointments = $this->getTotalAppointments($dateFrom, $dateTo);
        $noShows = DB::table('appointments')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->where('status', 'no-show')
            ->count();

        return $totalAppointments > 0 ? round(($noShows / $totalAppointments) * 100, 2) : 0;
    }

    private function getAverageWaitTime($dateFrom, $dateTo)
    {
        return DB::table('appointments')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->whereNotNull('wait_time_minutes')
            ->avg('wait_time_minutes') ?? 0;
    }
}
