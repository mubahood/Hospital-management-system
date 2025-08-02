<?php

namespace App\Repositories;

use App\Models\Consultation;
use App\Repositories\Contracts\ConsultationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConsultationRepository extends BaseRepository implements ConsultationRepositoryInterface
{
    /**
     * @var Consultation
     */
    protected $model;

    /**
     * Cache prefix for this repository
     */
    protected string $cachePrefix = 'consultation_';

    /**
     * Default cache TTL in minutes
     */
    protected int $cacheTtl = 60;

    /**
     * Relations to eager load by default
     */
    protected array $defaultRelations = ['patient', 'enterprise'];

    public function __construct(Consultation $model)
    {
        parent::__construct($model);
    }

    /**
     * Get consultations by patient ID
     */
    public function getByPatientId(int $patientId, array $relations = []): Collection
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, $patientId, $relations);
        
        return $this->cache($cacheKey, function () use ($patientId, $relations) {
            return $this->model->with($this->mergeRelations($relations))
                ->where('patient_id', $patientId)
                ->forCurrentEnterprise()
                ->orderBy('consultation_date', 'desc')
                ->get();
        });
    }

    /**
     * Get consultations by doctor ID
     */
    public function getByDoctorId(int $doctorId, array $relations = []): Collection
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, $doctorId, $relations);
        
        return $this->cache($cacheKey, function () use ($doctorId, $relations) {
            return $this->model->with($this->mergeRelations($relations))
                ->where('doctor_id', $doctorId)
                ->forCurrentEnterprise()
                ->orderBy('consultation_date', 'desc')
                ->get();
        });
    }

    /**
     * Get consultations by department
     */
    public function getByDepartment(int $departmentId, array $relations = []): Collection
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, $departmentId, $relations);
        
        return $this->cache($cacheKey, function () use ($departmentId, $relations) {
            return $this->model->with($this->mergeRelations($relations))
                ->where('department_id', $departmentId)
                ->forCurrentEnterprise()
                ->orderBy('consultation_date', 'desc')
                ->get();
        });
    }

    /**
     * Get consultations within date range
     */
    public function getByDateRange(Carbon $startDate, Carbon $endDate, array $relations = []): Collection
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')], $relations);
        
        return $this->cache($cacheKey, function () use ($startDate, $endDate, $relations) {
            return $this->model->with($this->mergeRelations($relations))
                ->whereBetween('consultation_date', [$startDate, $endDate])
                ->forCurrentEnterprise()
                ->orderBy('consultation_date', 'desc')
                ->get();
        }, $this->cacheTtl / 2); // Shorter cache for date-based queries
    }

    /**
     * Get consultations by status
     */
    public function getByStatus(string $status, array $relations = []): Collection
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, $status, $relations);
        
        return $this->cache($cacheKey, function () use ($status, $relations) {
            return $this->model->with($this->mergeRelations($relations))
                ->where('status', $status)
                ->forCurrentEnterprise()
                ->orderBy('consultation_date', 'desc')
                ->get();
        });
    }

    /**
     * Get upcoming consultations
     */
    public function getUpcoming(int $limit = 10, array $relations = []): Collection
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, $limit, $relations);
        
        return $this->cache($cacheKey, function () use ($limit, $relations) {
            return $this->model->with($this->mergeRelations($relations))
                ->where('consultation_date', '>', now())
                ->forCurrentEnterprise()
                ->orderBy('consultation_date', 'asc')
                ->limit($limit)
                ->get();
        }, 15); // Short cache for upcoming items
    }

    /**
     * Get today's consultations
     */
    public function getTodaysConsultations(array $relations = []): Collection
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, today()->format('Y-m-d'), $relations);
        
        return $this->cache($cacheKey, function () use ($relations) {
            return $this->model->with($this->mergeRelations($relations))
                ->whereDate('consultation_date', today())
                ->forCurrentEnterprise()
                ->orderBy('consultation_time', 'asc')
                ->get();
        }, 30); // 30-minute cache for today's data
    }

    /**
     * Get consultation statistics for date range
     */
    public function getStatisticsForDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        
        return $this->cache($cacheKey, function () use ($startDate, $endDate) {
            $baseQuery = $this->model->whereBetween('consultation_date', [$startDate, $endDate])
                ->forCurrentEnterprise();

            return [
                'total_consultations' => $baseQuery->count(),
                'completed_consultations' => $baseQuery->where('status', 'completed')->count(),
                'pending_consultations' => $baseQuery->where('status', 'pending')->count(),
                'cancelled_consultations' => $baseQuery->where('status', 'cancelled')->count(),
                'total_revenue' => $baseQuery->sum('consultation_fee'),
                'average_consultation_fee' => $baseQuery->avg('consultation_fee'),
                'unique_patients' => $baseQuery->distinct('patient_id')->count(),
                'unique_doctors' => $baseQuery->distinct('doctor_id')->count(),
            ];
        }, 120); // 2-hour cache for statistics
    }

    /**
     * Get most active doctors
     */
    public function getMostActiveDoctors(int $limit = 10): Collection
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, $limit);
        
        return $this->cache($cacheKey, function () use ($limit) {
            return $this->model->select('doctor_id', DB::raw('COUNT(*) as consultation_count'))
                ->with(['doctor'])
                ->forCurrentEnterprise()
                ->groupBy('doctor_id')
                ->orderBy('consultation_count', 'desc')
                ->limit($limit)
                ->get();
        }, 240); // 4-hour cache for activity stats
    }

    /**
     * Get consultation revenue for period
     */
    public function getRevenueForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        
        return $this->cache($cacheKey, function () use ($startDate, $endDate) {
            return (float) $this->model->whereBetween('consultation_date', [$startDate, $endDate])
                ->forCurrentEnterprise()
                ->sum('consultation_fee');
        }, 120); // 2-hour cache for revenue
    }

    /**
     * Get consultations with pending payments
     */
    public function getWithPendingPayments(array $relations = []): Collection
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, 'pending_payments', $relations);
        
        return $this->cache($cacheKey, function () use ($relations) {
            return $this->model->with($this->mergeRelations($relations))
                ->where('payment_status', 'pending')
                ->forCurrentEnterprise()
                ->orderBy('consultation_date', 'desc')
                ->get();
        }, 30); // Short cache for pending items
    }

    /**
     * Mark consultation as completed
     */
    public function markAsCompleted(int $consultationId, array $additionalData = []): bool
    {
        $consultation = $this->find($consultationId);
        
        if (!$consultation) {
            return false;
        }

        $updateData = array_merge([
            'status' => 'completed',
            'completed_at' => now(),
        ], $additionalData);

        $result = $consultation->update($updateData);
        
        if ($result) {
            $this->clearSpecificCache($consultationId);
            $this->clearModelCache(); // Clear general caches
        }

        return $result;
    }

    /**
     * Schedule follow-up consultation
     */
    public function scheduleFollowUp(int $originalConsultationId, array $followUpData): Consultation
    {
        $originalConsultation = $this->find($originalConsultationId, ['patient']);
        
        $followUpData = array_merge([
            'patient_id' => $originalConsultation->patient_id,
            'doctor_id' => $originalConsultation->doctor_id,
            'department_id' => $originalConsultation->department_id,
            'parent_consultation_id' => $originalConsultationId,
            'consultation_type' => 'follow_up',
            'status' => 'scheduled',
        ], $followUpData);

        $followUp = $this->create($followUpData);
        
        // Clear relevant caches
        $this->clearSpecificCache($originalConsultationId);
        $this->clearModelCache();

        return $followUp;
    }

    /**
     * Get patient consultation history
     */
    public function getPatientHistory(int $patientId, int $limit = 20): LengthAwarePaginator
    {
        return $this->model->with($this->defaultRelations)
            ->where('patient_id', $patientId)
            ->forCurrentEnterprise()
            ->orderBy('consultation_date', 'desc')
            ->paginate($limit);
    }

    /**
     * Get doctor's consultation schedule
     */
    public function getDoctorSchedule(int $doctorId, Carbon $date): Collection
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, [$doctorId, $date->format('Y-m-d')]);
        
        return $this->cache($cacheKey, function () use ($doctorId, $date) {
            return $this->model->with(['patient', 'department'])
                ->where('doctor_id', $doctorId)
                ->whereDate('consultation_date', $date)
                ->forCurrentEnterprise()
                ->orderBy('consultation_time', 'asc')
                ->get();
        }, 60); // 1-hour cache for schedules
    }
}
