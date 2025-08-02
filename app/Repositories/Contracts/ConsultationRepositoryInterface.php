<?php

namespace App\Repositories\Contracts;

use App\Models\Consultation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

interface ConsultationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get consultations by patient ID
     */
    public function getByPatientId(int $patientId, array $relations = []): Collection;

    /**
     * Get consultations by doctor ID
     */
    public function getByDoctorId(int $doctorId, array $relations = []): Collection;

    /**
     * Get consultations by department
     */
    public function getByDepartment(int $departmentId, array $relations = []): Collection;

    /**
     * Get consultations within date range
     */
    public function getByDateRange(Carbon $startDate, Carbon $endDate, array $relations = []): Collection;

    /**
     * Get consultations by status
     */
    public function getByStatus(string $status, array $relations = []): Collection;

    /**
     * Get upcoming consultations
     */
    public function getUpcoming(int $limit = 10, array $relations = []): Collection;

    /**
     * Get today's consultations
     */
    public function getTodaysConsultations(array $relations = []): Collection;

    /**
     * Get consultation statistics for date range
     */
    public function getStatisticsForDateRange(Carbon $startDate, Carbon $endDate): array;

    /**
     * Get most active doctors
     */
    public function getMostActiveDoctors(int $limit = 10): Collection;

    /**
     * Get consultation revenue for period
     */
    public function getRevenueForPeriod(Carbon $startDate, Carbon $endDate): float;

    /**
     * Get consultations with pending payments
     */
    public function getWithPendingPayments(array $relations = []): Collection;

    /**
     * Mark consultation as completed
     */
    public function markAsCompleted(int $consultationId, array $additionalData = []): bool;

    /**
     * Schedule follow-up consultation
     */
    public function scheduleFollowUp(int $originalConsultationId, array $followUpData): Consultation;

    /**
     * Get patient consultation history
     */
    public function getPatientHistory(int $patientId, int $limit = 20): LengthAwarePaginator;

    /**
     * Get doctor's consultation schedule
     */
    public function getDoctorSchedule(int $doctorId, Carbon $date): Collection;
}
