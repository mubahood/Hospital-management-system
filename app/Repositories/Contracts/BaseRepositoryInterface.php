<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * BaseRepositoryInterface - Core repository contract
 * 
 * Defines the standard interface that all repositories must implement
 * Provides common CRUD operations and enterprise-aware functionality
 */
interface BaseRepositoryInterface
{
    /**
     * Get all records with optional filtering
     */
    public function all(array $columns = ['*'], array $filters = []): Collection;

    /**
     * Find a record by ID
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find a record by ID or fail
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Find records by specific criteria
     */
    public function findBy(array $criteria, array $columns = ['*']): Collection;

    /**
     * Find first record by criteria
     */
    public function findOneBy(array $criteria, array $columns = ['*']): ?Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete a record
     */
    public function delete(Model $model): bool;

    /**
     * Get paginated results
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    /**
     * Count records with optional criteria
     */
    public function count(array $criteria = []): int;

    /**
     * Check if record exists
     */
    public function exists(array $criteria): bool;

    /**
     * Get records with relationships
     */
    public function with(array $relations): self;

    /**
     * Get records for specific enterprise
     */
    public function forEnterprise(int $enterpriseId): self;

    /**
     * Apply custom query scope
     */
    public function scope(string $scope, ...$parameters): self;

    /**
     * Order results
     */
    public function orderBy(string $column, string $direction = 'asc'): self;

    /**
     * Limit results
     */
    public function limit(int $limit): self;

    /**
     * Get fresh instance (reset query builder)
     */
    public function fresh(): self;
}
