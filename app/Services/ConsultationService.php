<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\MedicalService;
use App\Models\BillingItem;
use App\Models\PaymentRecord;
use App\Models\DoseItem;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\Contracts\ConsultationRepositoryInterface;
use App\Repositories\Contracts\BillingRepositoryInterface;
use App\Repositories\Contracts\PatientRepositoryInterface;
use App\Services\CacheService;
use App\Services\QueryOptimizationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * ConsultationService - Handles complex consultation business logic
 * 
 * This service encapsulates all consultation-related operations including:
 * - Consultation creation and management
 * - Medical service integration
 * - Billing and payment coordination
 * - Patient care workflow
 */
class ConsultationService
{
    protected ConsultationRepositoryInterface $consultationRepository;
    protected BillingRepositoryInterface $billingRepository;
    protected PatientRepositoryInterface $patientRepository;
    protected CacheService $cacheService;
    protected QueryOptimizationService $queryOptimization;

    public function __construct(
        ConsultationRepositoryInterface $consultationRepository,
        BillingRepositoryInterface $billingRepository,
        PatientRepositoryInterface $patientRepository,
        CacheService $cacheService,
        QueryOptimizationService $queryOptimization
    ) {
        $this->consultationRepository = $consultationRepository;
        $this->billingRepository = $billingRepository;
        $this->patientRepository = $patientRepository;
        $this->cacheService = $cacheService;
        $this->queryOptimization = $queryOptimization;
    }
    /**
     * Create a new consultation with all related data
     */
    public function createConsultation(array $data): Consultation
    {
        return DB::transaction(function () use ($data) {
            // Generate consultation number if not provided
            if (empty($data['consultation_number'])) {
                $data['consultation_number'] = $this->generateConsultationNumber();
            }

            // Calculate BMI if height and weight provided
            if (!empty($data['height']) && !empty($data['weight'])) {
                $data['bmi'] = $this->calculateBMI($data['weight'], $data['height']);
            }

            // Set default status if not provided
            $data['status'] = $data['status'] ?? 'Scheduled';

            // Create the consultation using repository
            $consultation = $this->consultationRepository->create($data);

            // Cache the consultation data
            $this->cacheService->cacheConsultation(
                $consultation->id,
                $consultation->enterprise_id,
                $consultation->load(['patient', 'enterprise', 'assignedTo'])->toArray()
            );

            // Log consultation creation
            Log::info('Consultation created', [
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'consultation_number' => $consultation->consultation_number
            ]);

            return $consultation->load(['patient', 'enterprise', 'assignedTo']);
        });
    }

    /**
     * Update consultation with validation and business rules
     */
    public function updateConsultation(Consultation $consultation, array $data): Consultation
    {
        return DB::transaction(function () use ($consultation, $data) {
            // Recalculate BMI if height or weight changed
            if (isset($data['height']) || isset($data['weight'])) {
                $height = $data['height'] ?? $consultation->height;
                $weight = $data['weight'] ?? $consultation->weight;
                
                if ($height && $weight) {
                    $data['bmi'] = $this->calculateBMI($weight, $height);
                }
            }

            // Update the consultation using repository
            $this->consultationRepository->update($consultation->id, $data);

            // Invalidate cache
            $this->cacheService->invalidateByTags([
                "consultation:{$consultation->id}",
                "patient:{$consultation->patient_id}",
                "enterprise:{$consultation->enterprise_id}"
            ]);

            // Log consultation update
            Log::info('Consultation updated', [
                'consultation_id' => $consultation->id,
                'updated_fields' => array_keys($data)
            ]);

            $updatedConsultation = $this->consultationRepository->find($consultation->id, ['*'])
                ->load(['patient', 'enterprise', 'assignedTo']);

            // Re-cache the updated consultation
            $this->cacheService->cacheConsultation(
                $updatedConsultation->id,
                $updatedConsultation->enterprise_id,
                $updatedConsultation->toArray()
            );

            return $updatedConsultation;
        });
    }

    /**
     * Add medical services to a consultation
     */
    public function addMedicalServices(Consultation $consultation, array $services): array
    {
        $createdServices = [];

        DB::transaction(function () use ($consultation, $services, &$createdServices) {
            foreach ($services as $serviceData) {
                $serviceData['consultation_id'] = $consultation->id;
                $serviceData['enterprise_id'] = $consultation->enterprise_id;
                $serviceData['patient_id'] = $consultation->patient_id;

                $medicalService = MedicalService::create($serviceData);
                $createdServices[] = $medicalService;

                // Auto-create billing item if price is set
                if (!empty($serviceData['price'])) {
                    $this->createBillingItem($consultation, $medicalService, $serviceData['price']);
                }
            }

            Log::info('Medical services added to consultation', [
                'consultation_id' => $consultation->id,
                'services_count' => count($createdServices)
            ]);
        });

        return $createdServices;
    }

    /**
     * Complete a consultation with all related finalizations
     */
    public function completeConsultation(Consultation $consultation, array $completionData = []): Consultation
    {
        return DB::transaction(function () use ($consultation, $completionData) {
            // Use repository method to mark as completed
            $this->consultationRepository->markAsCompleted($consultation->id, [
                'consultation_end_date_time' => $completionData['end_time'] ?? now(),
                'summary' => $completionData['summary'] ?? $consultation->summary,
                'recommendations' => $completionData['recommendations'] ?? $consultation->recommendations
            ]);

            // Mark all medical services as completed
            $consultation->medicalServices()->update([
                'status' => 'Completed',
                'completed_at' => now()
            ]);

            // Generate final billing if not exists
            $this->generateFinalBilling($consultation);

            Log::info('Consultation completed', [
                'consultation_id' => $consultation->id,
                'completion_time' => now()
            ]);

            return $this->consultationRepository->find($consultation->id, ['*']);
        });
    }

    /**
     * Get consultation analytics for dashboard
     */
    public function getConsultationAnalytics(int $enterpriseId, array $filters = []): array
    {
        // Try to get from cache first
        $cacheKey = 'analytics:' . md5(json_encode($filters));
        
        return $this->cacheService->remember(
            $this->cacheService->key(CacheService::PREFIX_STATS, $cacheKey, $enterpriseId),
            function () use ($enterpriseId, $filters) {
                return $this->queryOptimization->getOptimizedStatistics($enterpriseId, [
                    'from' => $filters['date_from'] ?? now()->subMonth(),
                    'to' => $filters['date_to'] ?? now()
                ]);
            },
            CacheService::TTL_SHORT,
            ['statistics', "enterprise:{$enterpriseId}"]
        );
    }

    /**
     * Get patient consultation history with medical services
     */
    public function getPatientHistory(int $patientId, int $enterpriseId): array
    {
        // Try to get from cache first
        return $this->cacheService->remember(
            $this->cacheService->key(CacheService::PREFIX_PATIENT, "{$patientId}:history", $enterpriseId),
            function () use ($patientId, $enterpriseId) {
                $historyData = $this->queryOptimization->loadPatientHistoryOptimized($patientId, 20);
                
                return [
                    'total_consultations' => $historyData['consultations']->count(),
                    'total_spent' => $historyData['total_spent'],
                    'total_paid' => $historyData['total_paid'],
                    'outstanding_balance' => $historyData['outstanding_balance'],
                    'last_consultation' => $historyData['consultations']->first(),
                    'consultation_history' => $historyData['consultations'],
                    'medical_summary' => $this->generateMedicalSummary($historyData['consultations'])
                ];
            },
            CacheService::TTL_MEDIUM,
            ['patients', "patient:{$patientId}", "enterprise:{$enterpriseId}"]
        );
    }

    /**
     * Generate unique consultation number
     */
    private function generateConsultationNumber(): string
    {
        $prefix = 'CON';
        $date = now()->format('Ymd');
        
        // Use repository to count existing consultations for today
        $lastNumber = $this->consultationRepository->count([
            ['consultation_number', 'like', $prefix . $date . '%']
        ]) + 1;

        return $prefix . $date . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate BMI from weight and height
     */
    private function calculateBMI(float $weight, float $height): float
    {
        // Convert height from cm to meters if necessary
        if ($height > 3) {
            $height = $height / 100;
        }

        return round($weight / ($height * $height), 2);
    }

    /**
     * Create billing item for medical service
     */
    private function createBillingItem(Consultation $consultation, MedicalService $medicalService, float $price): BillingItem
    {
        return BillingItem::create([
            'consultation_id' => $consultation->id,
            'enterprise_id' => $consultation->enterprise_id,
            'patient_id' => $consultation->patient_id,
            'medical_service_id' => $medicalService->id,
            'name' => $medicalService->name ?: 'Medical Service',
            'price' => $price,
            'quantity' => 1,
            'total' => $price,
            'billing_type' => 'Service',
            'status' => 'Pending'
        ]);
    }

    /**
     * Generate final billing for consultation
     */
    private function generateFinalBilling(Consultation $consultation): void
    {
        $totalAmount = $consultation->billingItems()->sum('total');
        
        if ($totalAmount > 0) {
            // Update consultation with total amount
            $consultation->update(['total_amount' => $totalAmount]);

            // Create payment record if not exists
            if ($consultation->paymentRecords()->count() === 0) {
                PaymentRecord::create([
                    'consultation_id' => $consultation->id,
                    'enterprise_id' => $consultation->enterprise_id,
                    'patient_id' => $consultation->patient_id,
                    'amount' => $totalAmount,
                    'balance' => $totalAmount,
                    'status' => 'Pending',
                    'payment_method' => 'Cash'
                ]);
            }
        }
    }

    /**
     * Get average consultation duration
     */
    private function getAverageConsultationDuration(int $enterpriseId, array $filters): float
    {
        // For now, use direct query but this could be moved to repository
        $query = Consultation::where('enterprise_id', $enterpriseId)
            ->whereNotNull('consultation_end_date_time');

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $query->where('consultation_date_time', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('consultation_date_time', '<=', $filters['date_to']);
        }

        $consultations = $query->select('consultation_date_time', 'consultation_end_date_time')->get();

        if ($consultations->isEmpty()) return 0;

        $totalMinutes = $consultations->sum(function ($consultation) {
            return Carbon::parse($consultation->consultation_end_date_time)
                ->diffInMinutes(Carbon::parse($consultation->consultation_date_time));
        });

        return round($totalMinutes / $consultations->count(), 2);
    }

    /**
     * Get consultation revenue
     */
    private function getConsultationRevenue($query): float
    {
        $consultationIds = $query->pluck('id');
        return PaymentRecord::whereIn('consultation_id', $consultationIds)
            ->where('status', 'Completed')
            ->sum('amount');
    }

    /**
     * Get top doctors by consultation count
     */
    private function getTopDoctorsByConsultations($query): array
    {
        return $query->select('assigned_to', DB::raw('count(*) as consultation_count'))
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->orderBy('consultation_count', 'desc')
            ->limit(5)
            ->with('assignedTo:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'doctor' => $item->assignedTo?->name ?? 'Unknown',
                    'consultation_count' => $item->consultation_count
                ];
            })
            ->toArray();
    }

    /**
     * Get monthly consultation trends
     */
    private function getMonthlyConsultationTrends(int $enterpriseId, array $filters): array
    {
        $months = 6; // Last 6 months
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $count = Consultation::where('enterprise_id', $enterpriseId)
                ->whereBetween('consultation_date_time', [$monthStart, $monthEnd])
                ->count();

            $trends[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }

        return $trends;
    }

    /**
     * Generate medical summary from consultation history
     */
    private function generateMedicalSummary($consultations): array
    {
        $summary = [
            'chronic_conditions' => [],
            'allergies' => [],
            'medications' => [],
            'recent_diagnoses' => []
        ];

        foreach ($consultations->take(10) as $consultation) {
            // Extract chronic conditions
            if (!empty($consultation->medical_history)) {
                $conditions = explode(',', $consultation->medical_history);
                $summary['chronic_conditions'] = array_merge($summary['chronic_conditions'], $conditions);
            }

            // Extract allergies
            if (!empty($consultation->allergies)) {
                $allergies = explode(',', $consultation->allergies);
                $summary['allergies'] = array_merge($summary['allergies'], $allergies);
            }

            // Extract recent diagnoses
            if (!empty($consultation->diagnosis)) {
                $summary['recent_diagnoses'][] = [
                    'diagnosis' => $consultation->diagnosis,
                    'date' => $consultation->consultation_date_time,
                    'doctor' => $consultation->assignedTo?->name
                ];
            }
        }

        // Remove duplicates and clean up
        $summary['chronic_conditions'] = array_unique(array_filter($summary['chronic_conditions']));
        $summary['allergies'] = array_unique(array_filter($summary['allergies']));

        return $summary;
    }
}
