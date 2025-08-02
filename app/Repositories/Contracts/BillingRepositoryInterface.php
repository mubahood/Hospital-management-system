<?php

namespace App\Repositories\Contracts;

use App\Models\BillingItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

interface BillingRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get billing items by patient ID
     */
    public function getByPatientId(int $patientId): Collection;

    /**
     * Get billing items by consultation ID
     */
    public function getByConsultationId(int $consultationId): Collection;

    /**
     * Get billing items by status
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get billing items by date range
     */
    public function getByDateRange(Carbon $startDate, Carbon $endDate): Collection;

    /**
     * Get pending payments
     */
    public function getPendingPayments(): Collection;

    /**
     * Get overdue payments
     */
    public function getOverduePayments(): Collection;

    /**
     * Calculate total revenue for period
     */
    public function getTotalRevenue(Carbon $startDate, Carbon $endDate): float;

    /**
     * Get revenue by service type
     */
    public function getRevenueByServiceType(Carbon $startDate, Carbon $endDate): array;

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(Carbon $startDate, Carbon $endDate): array;

    /**
     * Mark bill as paid
     */
    public function markAsPaid(int $billingItemId, array $paymentData): bool;

    /**
     * Apply discount to billing item
     */
    public function applyDiscount(int $billingItemId, float $discountAmount, string $reason = ''): bool;

    /**
     * Generate invoice for patient
     */
    public function generateInvoice(int $patientId, array $itemIds): array;

    /**
     * Get top revenue generating services
     */
    public function getTopRevenueServices(int $limit = 10): Collection;

    /**
     * Get billing summary for patient
     */
    public function getPatientBillingSummary(int $patientId): array;
}
