<?php

namespace App\Services;

use App\Models\BillingItem;
use App\Models\PaymentRecord;
use App\Models\Consultation;
use App\Models\MedicalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * BillingService - Handles complex billing and payment business logic
 * 
 * This service manages:
 * - Billing calculations and invoice generation
 * - Payment processing and reconciliation
 * - Financial reporting and analytics
 * - Insurance and discount management
 */
class BillingService
{
    /**
     * Create comprehensive billing for a consultation
     */
    public function createConsultationBilling(Consultation $consultation, array $billingData = []): array
    {
        return DB::transaction(function () use ($consultation, $billingData) {
            $billingItems = [];
            $totalAmount = 0;

            // Create billing items for medical services
            foreach ($consultation->medicalServices as $service) {
                if ($service->price > 0) {
                    $billingItem = $this->createBillingItem([
                        'consultation_id' => $consultation->id,
                        'enterprise_id' => $consultation->enterprise_id,
                        'patient_id' => $consultation->patient_id,
                        'medical_service_id' => $service->id,
                        'name' => $service->name ?: 'Medical Service',
                        'price' => $service->price,
                        'quantity' => $service->quantity ?? 1,
                        'total' => $service->price * ($service->quantity ?? 1),
                        'billing_type' => 'Service',
                        'status' => 'Pending'
                    ]);

                    $billingItems[] = $billingItem;
                    $totalAmount += $billingItem->total;
                }
            }

            // Add consultation fee if specified
            if (!empty($billingData['consultation_fee'])) {
                $consultationBilling = $this->createBillingItem([
                    'consultation_id' => $consultation->id,
                    'enterprise_id' => $consultation->enterprise_id,
                    'patient_id' => $consultation->patient_id,
                    'name' => 'Consultation Fee',
                    'price' => $billingData['consultation_fee'],
                    'quantity' => 1,
                    'total' => $billingData['consultation_fee'],
                    'billing_type' => 'Consultation',
                    'status' => 'Pending'
                ]);

                $billingItems[] = $consultationBilling;
                $totalAmount += $consultationBilling->total;
            }

            // Apply discounts if specified
            if (!empty($billingData['discount'])) {
                $discountAmount = $this->calculateDiscount($totalAmount, $billingData['discount']);
                if ($discountAmount > 0) {
                    $discountBilling = $this->createBillingItem([
                        'consultation_id' => $consultation->id,
                        'enterprise_id' => $consultation->enterprise_id,
                        'patient_id' => $consultation->patient_id,
                        'name' => 'Discount Applied',
                        'price' => -$discountAmount,
                        'quantity' => 1,
                        'total' => -$discountAmount,
                        'billing_type' => 'Discount',
                        'status' => 'Applied'
                    ]);

                    $billingItems[] = $discountBilling;
                    $totalAmount -= $discountAmount;
                }
            }

            // Update consultation with total amount
            $consultation->update(['total_amount' => $totalAmount]);

            Log::info('Consultation billing created', [
                'consultation_id' => $consultation->id,
                'total_amount' => $totalAmount,
                'items_count' => count($billingItems)
            ]);

            return [
                'billing_items' => $billingItems,
                'total_amount' => $totalAmount,
                'consultation' => $consultation->fresh()
            ];
        });
    }

    /**
     * Process payment for consultation
     */
    public function processPayment(Consultation $consultation, array $paymentData): PaymentRecord
    {
        return DB::transaction(function () use ($consultation, $paymentData) {
            // Calculate outstanding balance
            $totalBilled = $consultation->billingItems()->sum('total');
            $totalPaid = $consultation->paymentRecords()->where('status', 'Completed')->sum('amount');
            $outstandingBalance = $totalBilled - $totalPaid;

            // Validate payment amount
            $paymentAmount = min($paymentData['amount'], $outstandingBalance);
            $newBalance = $outstandingBalance - $paymentAmount;

            // Create payment record
            $payment = PaymentRecord::create([
                'consultation_id' => $consultation->id,
                'enterprise_id' => $consultation->enterprise_id,
                'patient_id' => $consultation->patient_id,
                'amount' => $paymentAmount,
                'balance' => $newBalance,
                'payment_method' => $paymentData['payment_method'] ?? 'Cash',
                'reference_number' => $paymentData['reference_number'] ?? $this->generatePaymentReference(),
                'status' => 'Completed',
                'created_by' => $paymentData['created_by'] ?? auth()->id(),
                'notes' => $paymentData['notes'] ?? null,
                'payment_date' => $paymentData['payment_date'] ?? now()
            ]);

            // Update billing items status if fully paid
            if ($newBalance <= 0) {
                $consultation->billingItems()->update(['status' => 'Paid']);
                $consultation->update(['payment_status' => 'Paid']);
            } else {
                $consultation->update(['payment_status' => 'Partially Paid']);
            }

            Log::info('Payment processed', [
                'payment_id' => $payment->id,
                'consultation_id' => $consultation->id,
                'amount' => $paymentAmount,
                'remaining_balance' => $newBalance
            ]);

            return $payment->load(['consultation', 'patient']);
        });
    }

    /**
     * Generate invoice for consultation
     */
    public function generateInvoice(Consultation $consultation): array
    {
        $billingItems = $consultation->billingItems()->get();
        $payments = $consultation->paymentRecords()->where('status', 'Completed')->get();

        $subtotal = $billingItems->where('billing_type', '!=', 'Discount')->sum('total');
        $discounts = abs($billingItems->where('billing_type', 'Discount')->sum('total'));
        $total = $billingItems->sum('total');
        $totalPaid = $payments->sum('amount');
        $balance = $total - $totalPaid;

        return [
            'invoice_number' => $this->generateInvoiceNumber($consultation),
            'consultation' => $consultation->load(['patient', 'enterprise', 'assignedTo']),
            'billing_items' => $billingItems->groupBy('billing_type'),
            'payments' => $payments,
            'financial_summary' => [
                'subtotal' => $subtotal,
                'discounts' => $discounts,
                'total' => $total,
                'total_paid' => $totalPaid,
                'balance' => $balance,
                'payment_status' => $balance <= 0 ? 'Paid' : ($totalPaid > 0 ? 'Partially Paid' : 'Pending')
            ],
            'generated_at' => now(),
            'due_date' => now()->addDays(30)
        ];
    }

    /**
     * Get billing analytics for enterprise
     */
    public function getBillingAnalytics(int $enterpriseId, array $filters = []): array
    {
        $query = BillingItem::where('enterprise_id', $enterpriseId);
        $paymentQuery = PaymentRecord::where('enterprise_id', $enterpriseId);

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
            $paymentQuery->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
            $paymentQuery->where('created_at', '<=', $filters['date_to']);
        }

        $totalBilled = $query->sum('total');
        $totalPaid = $paymentQuery->where('status', 'Completed')->sum('amount');
        $outstandingBalance = $totalBilled - $totalPaid;

        return [
            'revenue_summary' => [
                'total_billed' => $totalBilled,
                'total_collected' => $totalPaid,
                'outstanding_balance' => $outstandingBalance,
                'collection_rate' => $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 2) : 0
            ],
            'billing_by_type' => $this->getBillingByType($query),
            'payment_methods' => $this->getPaymentMethodBreakdown($paymentQuery),
            'monthly_trends' => $this->getMonthlyBillingTrends($enterpriseId, $filters),
            'top_services' => $this->getTopBilledServices($query),
            'aging_report' => $this->getAgingReport($enterpriseId)
        ];
    }

    /**
     * Get outstanding balances report
     */
    public function getOutstandingBalances(int $enterpriseId, array $filters = []): array
    {
        $consultations = Consultation::where('enterprise_id', $enterpriseId)
            ->with(['patient', 'billingItems', 'paymentRecords'])
            ->get();

        $outstandingConsultations = [];

        foreach ($consultations as $consultation) {
            $totalBilled = $consultation->billingItems->sum('total');
            $totalPaid = $consultation->paymentRecords->where('status', 'Completed')->sum('amount');
            $balance = $totalBilled - $totalPaid;

            if ($balance > 0) {
                $daysPending = Carbon::parse($consultation->consultation_date_time)->diffInDays(now());
                
                $outstandingConsultations[] = [
                    'consultation' => $consultation,
                    'patient' => $consultation->patient,
                    'total_billed' => $totalBilled,
                    'total_paid' => $totalPaid,
                    'outstanding_balance' => $balance,
                    'days_pending' => $daysPending,
                    'aging_category' => $this->getAgingCategory($daysPending)
                ];
            }
        }

        // Sort by days pending (oldest first)
        usort($outstandingConsultations, function ($a, $b) {
            return $b['days_pending'] <=> $a['days_pending'];
        });

        return [
            'outstanding_consultations' => $outstandingConsultations,
            'summary' => [
                'total_outstanding' => array_sum(array_column($outstandingConsultations, 'outstanding_balance')),
                'count' => count($outstandingConsultations),
                'aging_breakdown' => $this->getAgingBreakdown($outstandingConsultations)
            ]
        ];
    }

    /**
     * Apply insurance coverage calculation
     */
    public function applyInsuranceCoverage(Consultation $consultation, array $insuranceData): array
    {
        return DB::transaction(function () use ($consultation, $insuranceData) {
            $totalBilled = $consultation->billingItems()->sum('total');
            $coveragePercentage = $insuranceData['coverage_percentage'] ?? 0;
            $maxCoverage = $insuranceData['max_coverage'] ?? null;
            
            // Calculate insurance coverage
            $insuranceCoverage = $totalBilled * ($coveragePercentage / 100);
            if ($maxCoverage && $insuranceCoverage > $maxCoverage) {
                $insuranceCoverage = $maxCoverage;
            }

            // Create insurance billing item (negative amount)
            if ($insuranceCoverage > 0) {
                $insuranceBilling = $this->createBillingItem([
                    'consultation_id' => $consultation->id,
                    'enterprise_id' => $consultation->enterprise_id,
                    'patient_id' => $consultation->patient_id,
                    'name' => 'Insurance Coverage - ' . ($insuranceData['provider'] ?? 'Insurance'),
                    'price' => -$insuranceCoverage,
                    'quantity' => 1,
                    'total' => -$insuranceCoverage,
                    'billing_type' => 'Insurance',
                    'status' => 'Applied',
                    'notes' => 'Coverage: ' . $coveragePercentage . '%'
                ]);

                $newTotal = $totalBilled - $insuranceCoverage;
                $consultation->update(['total_amount' => $newTotal]);

                Log::info('Insurance coverage applied', [
                    'consultation_id' => $consultation->id,
                    'coverage_amount' => $insuranceCoverage,
                    'new_total' => $newTotal
                ]);

                return [
                    'insurance_billing' => $insuranceBilling,
                    'coverage_amount' => $insuranceCoverage,
                    'patient_responsibility' => $newTotal,
                    'consultation' => $consultation->fresh()
                ];
            }

            return ['message' => 'No insurance coverage applied'];
        });
    }

    /**
     * Create a billing item with validation
     */
    private function createBillingItem(array $data): BillingItem
    {
        // Set default values
        $data['status'] = $data['status'] ?? 'Pending';
        $data['created_by'] = $data['created_by'] ?? auth()->id();

        return BillingItem::create($data);
    }

    /**
     * Calculate discount amount
     */
    private function calculateDiscount(float $amount, $discount): float
    {
        if (is_numeric($discount)) {
            // If discount is a percentage (0-100)
            if ($discount <= 100) {
                return $amount * ($discount / 100);
            }
            // If discount is a fixed amount
            return min($discount, $amount);
        }

        return 0;
    }

    /**
     * Generate payment reference number
     */
    private function generatePaymentReference(): string
    {
        return 'PAY' . now()->format('YmdHis') . rand(100, 999);
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber(Consultation $consultation): string
    {
        return 'INV-' . $consultation->consultation_number;
    }

    /**
     * Get billing breakdown by type
     */
    private function getBillingByType($query): array
    {
        return $query->select('billing_type', DB::raw('SUM(total) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('billing_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->billing_type => [
                    'total' => $item->total_amount,
                    'count' => $item->count
                ]];
            })
            ->toArray();
    }

    /**
     * Get payment method breakdown
     */
    private function getPaymentMethodBreakdown($query): array
    {
        return $query->where('status', 'Completed')
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->payment_method => [
                    'total' => $item->total_amount,
                    'count' => $item->count
                ]];
            })
            ->toArray();
    }

    /**
     * Get monthly billing trends
     */
    private function getMonthlyBillingTrends(int $enterpriseId, array $filters): array
    {
        $months = 12; // Last 12 months
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $billed = BillingItem::where('enterprise_id', $enterpriseId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total');

            $collected = PaymentRecord::where('enterprise_id', $enterpriseId)
                ->where('status', 'Completed')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');

            $trends[] = [
                'month' => $date->format('M Y'),
                'billed' => $billed,
                'collected' => $collected,
                'collection_rate' => $billed > 0 ? round(($collected / $billed) * 100, 2) : 0
            ];
        }

        return $trends;
    }

    /**
     * Get top billed services
     */
    private function getTopBilledServices($query): array
    {
        return $query->select('name', DB::raw('SUM(total) as total_revenue'), DB::raw('COUNT(*) as count'))
            ->where('billing_type', 'Service')
            ->groupBy('name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get aging report
     */
    private function getAgingReport(int $enterpriseId): array
    {
        $aging = [
            '0-30 days' => 0,
            '31-60 days' => 0,
            '61-90 days' => 0,
            '90+ days' => 0
        ];

        $consultations = Consultation::where('enterprise_id', $enterpriseId)
            ->with(['billingItems', 'paymentRecords'])
            ->get();

        foreach ($consultations as $consultation) {
            $totalBilled = $consultation->billingItems->sum('total');
            $totalPaid = $consultation->paymentRecords->where('status', 'Completed')->sum('amount');
            $balance = $totalBilled - $totalPaid;

            if ($balance > 0) {
                $daysPending = Carbon::parse($consultation->consultation_date_time)->diffInDays(now());
                $category = $this->getAgingCategory($daysPending);
                $aging[$category] += $balance;
            }
        }

        return $aging;
    }

    /**
     * Get aging category for days pending
     */
    private function getAgingCategory(int $days): string
    {
        if ($days <= 30) return '0-30 days';
        if ($days <= 60) return '31-60 days';
        if ($days <= 90) return '61-90 days';
        return '90+ days';
    }

    /**
     * Get aging breakdown from outstanding consultations
     */
    private function getAgingBreakdown(array $outstandingConsultations): array
    {
        $breakdown = [
            '0-30 days' => ['count' => 0, 'amount' => 0],
            '31-60 days' => ['count' => 0, 'amount' => 0],
            '61-90 days' => ['count' => 0, 'amount' => 0],
            '90+ days' => ['count' => 0, 'amount' => 0]
        ];

        foreach ($outstandingConsultations as $consultation) {
            $category = $consultation['aging_category'];
            $breakdown[$category]['count']++;
            $breakdown[$category]['amount'] += $consultation['outstanding_balance'];
        }

        return $breakdown;
    }
}
