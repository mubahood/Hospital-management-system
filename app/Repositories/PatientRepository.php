<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Repositories\Contracts\PatientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class PatientRepository extends BaseRepository implements PatientRepositoryInterface
{
    protected string $cachePrefix = 'patient_';
    protected int $cacheTtl = 120; // 2 hours cache
    protected array $defaultRelations = ['enterprise'];

    public function __construct(Patient $model)
    {
        parent::__construct($model);
    }

    public function search(string $term): Collection
    {
        return $this->model->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%")
                  ->orWhere('patient_number', 'like', "%{$term}%");
        })->get();
    }

    public function getByAgeRange(int $minAge, int $maxAge): Collection
    {
        $minDate = now()->subYears($maxAge);
        $maxDate = now()->subYears($minAge);
        
        return $this->model->whereBetween('date_of_birth', [$minDate, $maxDate])->get();
    }

    public function getByGender(string $gender): Collection
    {
        return $this->model->where('gender', $gender)->get();
    }

    public function getByBloodGroup(string $bloodGroup): Collection
    {
        return $this->model->where('blood_group', $bloodGroup)->get();
    }

    public function getWithUpcomingAppointments(): Collection
    {
        return $this->model->whereHas('consultations', function ($query) {
            $query->where('consultation_date_time', '>', now())
                  ->where('status', 'Scheduled');
        })->get();
    }

    public function getByRegistrationDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    public function getRecentlyRegistered(int $limit = 10): Collection
    {
        return $this->model->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    public function getStatistics(): array
    {
        $total = $this->model->count();
        
        return [
            'total_patients' => $total,
            'male_patients' => $this->model->where('gender', 'Male')->count(),
            'female_patients' => $this->model->where('gender', 'Female')->count(),
            'registered_this_month' => $this->model->whereMonth('created_at', now()->month)->count(),
            'active_patients' => $this->model->whereHas('consultations', function ($query) {
                $query->where('created_at', '>=', now()->subMonths(6));
            })->count()
        ];
    }

    public function getMedicalHistory(int $patientId): Collection
    {
        $patient = $this->find($patientId);
        return $patient ? $patient->consultations()->with(['medicalServices', 'assignedTo'])->get() : collect();
    }

    public function updateProfile(int $patientId, array $data): bool
    {
        return $this->update($patientId, $data);
    }

    public function getWithPendingBills(): Collection
    {
        return $this->model->whereHas('billingItems', function ($query) {
            $query->where('status', 'Pending');
        })->get();
    }

    public function archive(int $patientId): bool
    {
        return $this->update($patientId, ['status' => 'Archived']);
    }

    public function restore(int $patientId): bool
    {
        return $this->update($patientId, ['status' => 'Active']);
    }
}
