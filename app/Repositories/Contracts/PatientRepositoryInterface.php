<?php

namespace App\Repositories\Contracts;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

interface PatientRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Search patients by various criteria
     */
    public function search(string $term): Collection;

    /**
     * Get patients by age range
     */
    public function getByAgeRange(int $minAge, int $maxAge): Collection;

    /**
     * Get patients by gender
     */
    public function getByGender(string $gender): Collection;

    /**
     * Get patients by blood group
     */
    public function getByBloodGroup(string $bloodGroup): Collection;

    /**
     * Get patients with upcoming appointments
     */
    public function getWithUpcomingAppointments(): Collection;

    /**
     * Get patients by registration date range
     */
    public function getByRegistrationDateRange(Carbon $startDate, Carbon $endDate): Collection;

    /**
     * Get most recently registered patients
     */
    public function getRecentlyRegistered(int $limit = 10): Collection;

    /**
     * Get patient statistics
     */
    public function getStatistics(): array;

    /**
     * Get patient medical history
     */
    public function getMedicalHistory(int $patientId): Collection;

    /**
     * Update patient profile
     */
    public function updateProfile(int $patientId, array $data): bool;

    /**
     * Get patients with pending bills
     */
    public function getWithPendingBills(): Collection;

    /**
     * Archive patient record
     */
    public function archive(int $patientId): bool;

    /**
     * Restore archived patient
     */
    public function restore(int $patientId): bool;
}
