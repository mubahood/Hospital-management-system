<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\MedicalService;
use App\Models\BillingItem;
use App\Models\PaymentRecord;
use App\Models\StockItem;
use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ReportService - Handles complex reporting and analytics business logic
 * 
 * This service generates:
 * - Financial reports and analytics
 * - Medical reports and statistics
 * - Operational reports and KPIs
 * - Custom report generation
 */
class ReportService
{
    /**
     * Generate comprehensive financial report
     */
    public function generateFinancialReport(int $enterpriseId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? now()->endOfMonth();

        // Revenue analysis
        $revenue = $this->getRevenueAnalysis($enterpriseId, $dateFrom, $dateTo);
        
        // Billing analysis
        $billing = $this->getBillingAnalysis($enterpriseId, $dateFrom, $dateTo);
        
        // Payment analysis
        $payments = $this->getPaymentAnalysis($enterpriseId, $dateFrom, $dateTo);
        
        // Outstanding balances
        $outstanding = $this->getOutstandingBalances($enterpriseId);

        return [
            'report_info' => [
                'enterprise_id' => $enterpriseId,
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo
                ],
                'generated_at' => now(),
                'generated_by' => auth()->user()?->name ?? 'System'
            ],
            'revenue_summary' => $revenue,
            'billing_analysis' => $billing,
            'payment_analysis' => $payments,
            'outstanding_balances' => $outstanding,
            'financial_kpis' => $this->calculateFinancialKPIs($revenue, $billing, $payments),
            'trends' => $this->getFinancialTrends($enterpriseId, $dateFrom, $dateTo)
        ];
    }

    /**
     * Generate medical services report
     */
    public function generateMedicalReport(int $enterpriseId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? now()->endOfMonth();

        $consultations = Consultation::where('enterprise_id', $enterpriseId)
            ->whereBetween('consultation_date_time', [$dateFrom, $dateTo])
            ->with(['medicalServices', 'patient', 'assignedTo'])
            ->get();

        $medicalServices = MedicalService::where('enterprise_id', $enterpriseId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['consultation.patient'])
            ->get();

        return [
            'report_info' => [
                'enterprise_id' => $enterpriseId,
                'period' => ['from' => $dateFrom, 'to' => $dateTo],
                'generated_at' => now()
            ],
            'consultation_summary' => [
                'total_consultations' => $consultations->count(),
                'completed_consultations' => $consultations->where('status', 'Completed')->count(),
                'pending_consultations' => $consultations->where('status', 'Scheduled')->count(),
                'cancelled_consultations' => $consultations->where('status', 'Cancelled')->count(),
                'average_consultation_duration' => $this->calculateAverageConsultationDuration($consultations)
            ],
            'medical_services_summary' => [
                'total_services' => $medicalServices->count(),
                'services_by_type' => $medicalServices->groupBy('service_type')->map->count(),
                'most_common_services' => $this->getMostCommonServices($medicalServices),
                'revenue_by_service' => $this->getRevenueByService($medicalServices)
            ],
            'doctor_performance' => $this->getDoctorPerformance($consultations),
            'patient_demographics' => $this->getPatientDemographics($consultations),
            'medical_trends' => $this->getMedicalTrends($enterpriseId, $dateFrom, $dateTo)
        ];
    }

    /**
     * Generate operational dashboard report
     */
    public function generateOperationalReport(int $enterpriseId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? now()->endOfMonth();

        return [
            'report_info' => [
                'enterprise_id' => $enterpriseId,
                'period' => ['from' => $dateFrom, 'to' => $dateTo],
                'generated_at' => now()
            ],
            'patient_flow' => $this->getPatientFlowMetrics($enterpriseId, $dateFrom, $dateTo),
            'resource_utilization' => $this->getResourceUtilization($enterpriseId, $dateFrom, $dateTo),
            'efficiency_metrics' => $this->getEfficiencyMetrics($enterpriseId, $dateFrom, $dateTo),
            'quality_indicators' => $this->getQualityIndicators($enterpriseId, $dateFrom, $dateTo),
            'staff_productivity' => $this->getStaffProductivity($enterpriseId, $dateFrom, $dateTo),
            'operational_kpis' => $this->getOperationalKPIs($enterpriseId, $dateFrom, $dateTo)
        ];
    }

    /**
     * Generate inventory report
     */
    public function generateInventoryReport(int $enterpriseId, array $filters = []): array
    {
        $stockItems = StockItem::where('enterprise_id', $enterpriseId)
            ->with(['category'])
            ->get();

        return [
            'report_info' => [
                'enterprise_id' => $enterpriseId,
                'generated_at' => now()
            ],
            'inventory_summary' => [
                'total_items' => $stockItems->count(),
                'total_value' => $stockItems->sum('total_value'),
                'low_stock_items' => $stockItems->filter(fn($item) => $item->current_quantity <= $item->minimum_quantity)->count(),
                'out_of_stock_items' => $stockItems->where('current_quantity', 0)->count()
            ],
            'category_analysis' => $stockItems->groupBy('category.name')->map(function ($items) {
                return [
                    'items_count' => $items->count(),
                    'total_value' => $items->sum('total_value'),
                    'low_stock_count' => $items->filter(fn($item) => $item->current_quantity <= $item->minimum_quantity)->count()
                ];
            }),
            'valuation_breakdown' => $this->getInventoryValuation($stockItems),
            'movement_analysis' => $this->getInventoryMovementAnalysis($enterpriseId),
            'reorder_recommendations' => $this->getReorderRecommendations($stockItems)
        ];
    }

    /**
     * Generate custom analytics report
     */
    public function generateCustomReport(int $enterpriseId, array $metrics, array $filters = []): array
    {
        $report = [
            'report_info' => [
                'enterprise_id' => $enterpriseId,
                'custom_metrics' => $metrics,
                'filters_applied' => $filters,
                'generated_at' => now()
            ],
            'data' => []
        ];

        foreach ($metrics as $metric) {
            switch ($metric) {
                case 'patient_satisfaction':
                    $report['data']['patient_satisfaction'] = $this->getPatientSatisfactionMetrics($enterpriseId, $filters);
                    break;
                case 'appointment_efficiency':
                    $report['data']['appointment_efficiency'] = $this->getAppointmentEfficiency($enterpriseId, $filters);
                    break;
                case 'revenue_per_patient':
                    $report['data']['revenue_per_patient'] = $this->getRevenuePerPatient($enterpriseId, $filters);
                    break;
                case 'service_popularity':
                    $report['data']['service_popularity'] = $this->getServicePopularity($enterpriseId, $filters);
                    break;
                case 'payment_conversion':
                    $report['data']['payment_conversion'] = $this->getPaymentConversion($enterpriseId, $filters);
                    break;
            }
        }

        return $report;
    }

    /**
     * Export report to various formats
     */
    public function exportReport(array $reportData, string $format = 'pdf'): array
    {
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($reportData);
            case 'csv':
                return $this->exportToCSV($reportData);
            case 'pdf':
                return $this->exportToPDF($reportData);
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }
    }

    /**
     * Get revenue analysis
     */
    private function getRevenueAnalysis(int $enterpriseId, $dateFrom, $dateTo): array
    {
        $payments = PaymentRecord::where('enterprise_id', $enterpriseId)
            ->where('status', 'Completed')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->get();

        $totalRevenue = $payments->sum('amount');
        $averageTransaction = $payments->avg('amount');
        $transactionCount = $payments->count();

        return [
            'total_revenue' => $totalRevenue,
            'transaction_count' => $transactionCount,
            'average_transaction' => $averageTransaction,
            'revenue_by_method' => $payments->groupBy('payment_method')->map->sum('amount'),
            'daily_revenue' => $this->getDailyRevenue($payments, $dateFrom, $dateTo),
            'revenue_growth' => $this->calculateRevenueGrowth($enterpriseId, $dateFrom, $dateTo)
        ];
    }

    /**
     * Get billing analysis
     */
    private function getBillingAnalysis(int $enterpriseId, $dateFrom, $dateTo): array
    {
        $billingItems = BillingItem::where('enterprise_id', $enterpriseId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        return [
            'total_billed' => $billingItems->sum('total'),
            'billing_count' => $billingItems->count(),
            'average_bill' => $billingItems->avg('total'),
            'billing_by_type' => $billingItems->groupBy('billing_type')->map->sum('total'),
            'billing_efficiency' => $this->calculateBillingEfficiency($billingItems)
        ];
    }

    /**
     * Get payment analysis
     */
    private function getPaymentAnalysis(int $enterpriseId, $dateFrom, $dateTo): array
    {
        $payments = PaymentRecord::where('enterprise_id', $enterpriseId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $completedPayments = $payments->where('status', 'Completed');
        $pendingPayments = $payments->where('status', 'Pending');

        return [
            'total_payments' => $payments->count(),
            'completed_payments' => $completedPayments->count(),
            'pending_payments' => $pendingPayments->count(),
            'completion_rate' => $payments->count() > 0 ? ($completedPayments->count() / $payments->count()) * 100 : 0,
            'average_payment_time' => $this->calculateAveragePaymentTime($completedPayments),
            'payment_methods_breakdown' => $completedPayments->groupBy('payment_method')->map->count()
        ];
    }

    /**
     * Get outstanding balances summary
     */
    private function getOutstandingBalances(int $enterpriseId): array
    {
        $consultations = Consultation::where('enterprise_id', $enterpriseId)
            ->with(['billingItems', 'paymentRecords'])
            ->get();

        $outstanding = [];
        $totalOutstanding = 0;

        foreach ($consultations as $consultation) {
            $billed = $consultation->billingItems->sum('total');
            $paid = $consultation->paymentRecords->where('status', 'Completed')->sum('amount');
            $balance = $billed - $paid;

            if ($balance > 0) {
                $outstanding[] = [
                    'consultation_id' => $consultation->id,
                    'patient_name' => $consultation->patient?->name ?? 'Unknown',
                    'billed_amount' => $billed,
                    'paid_amount' => $paid,
                    'outstanding_balance' => $balance,
                    'days_overdue' => Carbon::parse($consultation->consultation_date_time)->diffInDays(now())
                ];
                $totalOutstanding += $balance;
            }
        }

        return [
            'total_outstanding' => $totalOutstanding,
            'outstanding_count' => count($outstanding),
            'outstanding_details' => $outstanding,
            'aging_analysis' => $this->getAgingAnalysis($outstanding)
        ];
    }

    /**
     * Calculate financial KPIs
     */
    private function calculateFinancialKPIs($revenue, $billing, $payments): array
    {
        return [
            'collection_efficiency' => $billing['total_billed'] > 0 ? ($revenue['total_revenue'] / $billing['total_billed']) * 100 : 0,
            'average_revenue_per_consultation' => $billing['billing_count'] > 0 ? $revenue['total_revenue'] / $billing['billing_count'] : 0,
            'payment_success_rate' => $payments['total_payments'] > 0 ? ($payments['completed_payments'] / $payments['total_payments']) * 100 : 0,
            'revenue_growth_rate' => $revenue['revenue_growth'] ?? 0
        ];
    }

    /**
     * Get financial trends
     */
    private function getFinancialTrends(int $enterpriseId, $dateFrom, $dateTo): array
    {
        $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo));
        $trends = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::parse($dateFrom)->addDays($i);
            
            $dayRevenue = PaymentRecord::where('enterprise_id', $enterpriseId)
                ->where('status', 'Completed')
                ->whereDate('payment_date', $date)
                ->sum('amount');

            $dayBilling = BillingItem::where('enterprise_id', $enterpriseId)
                ->whereDate('created_at', $date)
                ->sum('total');

            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'revenue' => $dayRevenue,
                'billing' => $dayBilling
            ];
        }

        return $trends;
    }

    /**
     * Get most common services
     */
    private function getMostCommonServices($medicalServices): array
    {
        return $medicalServices->groupBy('name')
            ->map(function ($services) {
                return [
                    'count' => $services->count(),
                    'total_revenue' => $services->sum('price')
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->toArray();
    }

    /**
     * Get revenue by service
     */
    private function getRevenueByService($medicalServices): array
    {
        return $medicalServices->groupBy('name')
            ->map->sum('price')
            ->sortByDesc(function ($revenue) {
                return $revenue;
            })
            ->take(10)
            ->toArray();
    }

    /**
     * Get doctor performance metrics
     */
    private function getDoctorPerformance($consultations): array
    {
        return $consultations->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->map(function ($doctorConsultations) {
                $completed = $doctorConsultations->where('status', 'Completed');
                return [
                    'total_consultations' => $doctorConsultations->count(),
                    'completed_consultations' => $completed->count(),
                    'completion_rate' => $doctorConsultations->count() > 0 ? ($completed->count() / $doctorConsultations->count()) * 100 : 0,
                    'average_duration' => $this->calculateAverageConsultationDuration($completed)
                ];
            })
            ->toArray();
    }

    /**
     * Get patient demographics
     */
    private function getPatientDemographics($consultations): array
    {
        $patients = $consultations->pluck('patient')->filter();

        return [
            'total_unique_patients' => $patients->unique('id')->count(),
            'gender_distribution' => $patients->groupBy('sex')->map->count(),
            'age_groups' => $this->getAgeGroups($patients),
            'new_vs_returning' => $this->getNewVsReturningPatients($consultations)
        ];
    }

    /**
     * Calculate average consultation duration
     */
    private function calculateAverageConsultationDuration($consultations): float
    {
        $consultationsWithEndTime = $consultations->whereNotNull('consultation_end_date_time');
        
        if ($consultationsWithEndTime->isEmpty()) {
            return 0;
        }

        $totalMinutes = $consultationsWithEndTime->sum(function ($consultation) {
            return Carbon::parse($consultation->consultation_end_date_time)
                ->diffInMinutes(Carbon::parse($consultation->consultation_date_time));
        });

        return $totalMinutes / $consultationsWithEndTime->count();
    }

    /**
     * Additional helper methods would continue here...
     * For brevity, I'll include key method signatures
     */
    
    private function getMedicalTrends(int $enterpriseId, $dateFrom, $dateTo): array { return []; }
    private function getPatientFlowMetrics(int $enterpriseId, $dateFrom, $dateTo): array { return []; }
    private function getResourceUtilization(int $enterpriseId, $dateFrom, $dateTo): array { return []; }
    private function getEfficiencyMetrics(int $enterpriseId, $dateFrom, $dateTo): array { return []; }
    private function getQualityIndicators(int $enterpriseId, $dateFrom, $dateTo): array { return []; }
    private function getStaffProductivity(int $enterpriseId, $dateFrom, $dateTo): array { return []; }
    private function getOperationalKPIs(int $enterpriseId, $dateFrom, $dateTo): array { return []; }
    private function getInventoryValuation($stockItems): array { return []; }
    private function getInventoryMovementAnalysis(int $enterpriseId): array { return []; }
    private function getReorderRecommendations($stockItems): array { return []; }
    
    // Export methods
    private function exportToExcel(array $reportData): array { return ['format' => 'excel', 'data' => $reportData]; }
    private function exportToCSV(array $reportData): array { return ['format' => 'csv', 'data' => $reportData]; }
    private function exportToPDF(array $reportData): array { return ['format' => 'pdf', 'data' => $reportData]; }
    
    // Additional analysis methods
    private function getDailyRevenue($payments, $dateFrom, $dateTo): array { return []; }
    private function calculateRevenueGrowth(int $enterpriseId, $dateFrom, $dateTo): float { return 0; }
    private function calculateBillingEfficiency($billingItems): float { return 0; }
    private function calculateAveragePaymentTime($payments): float { return 0; }
    private function getAgingAnalysis($outstanding): array { return []; }
    private function getAgeGroups($patients): array { return []; }
    private function getNewVsReturningPatients($consultations): array { return []; }
    
    // Custom metrics
    private function getPatientSatisfactionMetrics(int $enterpriseId, array $filters): array { return []; }
    private function getAppointmentEfficiency(int $enterpriseId, array $filters): array { return []; }
    private function getRevenuePerPatient(int $enterpriseId, array $filters): array { return []; }
    private function getServicePopularity(int $enterpriseId, array $filters): array { return []; }
    private function getPaymentConversion(int $enterpriseId, array $filters): array { return []; }
}
