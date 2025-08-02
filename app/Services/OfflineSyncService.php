<?php

namespace App\Services;

use App\Models\SyncRecord;
use App\Models\SyncQueue;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OfflineSyncService
{
    /**
     * Sync offline data from mobile device
     *
     * @param array $offlineData
     * @param int $userId
     * @param int $enterpriseId
     * @param string $deviceId
     * @return array
     */
    public function syncFromDevice(array $offlineData, int $userId, int $enterpriseId, string $deviceId): array
    {
        $results = [
            'synced' => [],
            'conflicts' => [],
            'errors' => [],
            'total_processed' => 0,
            'successful_syncs' => 0
        ];

        DB::beginTransaction();
        
        try {
            foreach ($offlineData as $item) {
                $results['total_processed']++;
                
                $syncResult = $this->processSingleRecord(
                    $item,
                    $userId,
                    $enterpriseId,
                    $deviceId
                );
                
                if ($syncResult['status'] === 'synced') {
                    $results['synced'][] = $syncResult;
                    $results['successful_syncs']++;
                } elseif ($syncResult['status'] === 'conflict') {
                    $results['conflicts'][] = $syncResult;
                } else {
                    $results['errors'][] = $syncResult;
                }
            }
            
            DB::commit();
            
            Log::info('Offline sync completed', [
                'user_id' => $userId,
                'enterprise_id' => $enterpriseId,
                'device_id' => $deviceId,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Offline sync failed', [
                'user_id' => $userId,
                'enterprise_id' => $enterpriseId,
                'device_id' => $deviceId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
        
        return $results;
    }

    /**
     * Process a single record for synchronization
     *
     * @param array $recordData
     * @param int $userId
     * @param int $enterpriseId
     * @param string $deviceId
     * @return array
     */
    private function processSingleRecord(array $recordData, int $userId, int $enterpriseId, string $deviceId): array
    {
        try {
            // Validate required fields
            if (!isset($recordData['table_name'], $recordData['operation'], $recordData['record_id'])) {
                return [
                    'status' => 'error',
                    'message' => 'Missing required fields',
                    'data' => $recordData
                ];
            }

            $tableName = $recordData['table_name'];
            $operation = $recordData['operation'];
            $recordId = $recordData['record_id'];
            $clientTimestamp = isset($recordData['client_timestamp']) 
                ? Carbon::parse($recordData['client_timestamp']) 
                : now();

            // Check for existing sync record
            $existingSyncRecord = SyncRecord::where('user_id', $userId)
                ->where('enterprise_id', $enterpriseId)
                ->where('device_id', $deviceId)
                ->where('table_name', $tableName)
                ->where('record_id', $recordId)
                ->first();

            // Detect conflicts
            if ($existingSyncRecord && $this->hasConflict($existingSyncRecord, $recordData)) {
                return $this->handleConflict($existingSyncRecord, $recordData, $userId, $enterpriseId, $deviceId);
            }

            // Process the operation
            $result = $this->executeOperation($tableName, $operation, $recordData, $enterpriseId);
            
            if ($result['success']) {
                // Create or update sync record
                $syncRecord = $existingSyncRecord ?: new SyncRecord();
                $syncRecord->fill([
                    'user_id' => $userId,
                    'enterprise_id' => $enterpriseId,
                    'device_id' => $deviceId,
                    'table_name' => $tableName,
                    'record_id' => $recordId,
                    'operation' => $operation,
                    'data' => $recordData['data'] ?? null,
                    'metadata' => $recordData['metadata'] ?? null,
                    'status' => SyncRecord::STATUS_SYNCED,
                    'client_timestamp' => $clientTimestamp,
                    'server_timestamp' => now(),
                    'client_version' => $recordData['client_version'] ?? null,
                    'sync_hash' => $this->generateSyncHash($recordData)
                ]);
                $syncRecord->save();

                return [
                    'status' => 'synced',
                    'sync_record_id' => $syncRecord->id,
                    'server_record_id' => $result['server_record_id'] ?? null,
                    'data' => $recordData
                ];
            } else {
                // Create failed sync record
                $syncRecord = new SyncRecord();
                $syncRecord->fill([
                    'user_id' => $userId,
                    'enterprise_id' => $enterpriseId,
                    'device_id' => $deviceId,
                    'table_name' => $tableName,
                    'record_id' => $recordId,
                    'operation' => $operation,
                    'data' => $recordData['data'] ?? null,
                    'status' => SyncRecord::STATUS_FAILED,
                    'error_message' => $result['error'] ?? 'Unknown error',
                    'client_timestamp' => $clientTimestamp,
                    'retry_count' => 0
                ]);
                $syncRecord->save();

                return [
                    'status' => 'error',
                    'message' => $result['error'] ?? 'Operation failed',
                    'sync_record_id' => $syncRecord->id,
                    'data' => $recordData
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error processing sync record', [
                'error' => $e->getMessage(),
                'data' => $recordData
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => $recordData
            ];
        }
    }

    /**
     * Execute the database operation (public for conflict resolution)
     *
     * @param string $tableName
     * @param string $operation
     * @param array $recordData
     * @param int $enterpriseId
     * @return array
     */
    public function executeOperation(string $tableName, string $operation, array $recordData, int $enterpriseId): array
    {
        try {
            $modelClass = $this->getModelClass($tableName);
            
            if (!$modelClass) {
                return [
                    'success' => false,
                    'error' => "Unsupported table: {$tableName}"
                ];
            }

            $data = $recordData['data'] ?? [];
            $data['enterprise_id'] = $enterpriseId; // Ensure enterprise scoping

            switch ($operation) {
                case 'create':
                    return $this->handleCreate($modelClass, $data, $recordData['record_id']);
                    
                case 'update':
                    return $this->handleUpdate($modelClass, $data, $recordData['record_id'], $enterpriseId);
                    
                case 'delete':
                    return $this->handleDelete($modelClass, $recordData['record_id'], $enterpriseId);
                    
                default:
                    return [
                        'success' => false,
                        'error' => "Unsupported operation: {$operation}"
                    ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle create operation
     */
    private function handleCreate(string $modelClass, array $data, int $clientRecordId): array
    {
        try {
            // Check if record with this client ID already exists
            $existingRecord = $modelClass::where('client_id', $clientRecordId)
                ->where('enterprise_id', $data['enterprise_id'])
                ->first();

            if ($existingRecord) {
                return [
                    'success' => true,
                    'server_record_id' => $existingRecord->id,
                    'message' => 'Record already exists'
                ];
            }

            // Add client_id to track the relationship
            $data['client_id'] = $clientRecordId;
            
            $record = $modelClass::create($data);

            return [
                'success' => true,
                'server_record_id' => $record->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle update operation
     */
    private function handleUpdate(string $modelClass, array $data, int $recordId, int $enterpriseId): array
    {
        try {
            // Try to find by server ID first, then by client ID
            $record = $modelClass::where('enterprise_id', $enterpriseId)
                ->where(function($query) use ($recordId) {
                    $query->where('id', $recordId)
                          ->orWhere('client_id', $recordId);
                })
                ->first();

            if (!$record) {
                return [
                    'success' => false,
                    'error' => 'Record not found'
                ];
            }

            $record->update($data);

            return [
                'success' => true,
                'server_record_id' => $record->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle delete operation
     */
    private function handleDelete(string $modelClass, int $recordId, int $enterpriseId): array
    {
        try {
            $record = $modelClass::where('enterprise_id', $enterpriseId)
                ->where(function($query) use ($recordId) {
                    $query->where('id', $recordId)
                          ->orWhere('client_id', $recordId);
                })
                ->first();

            if (!$record) {
                return [
                    'success' => true,
                    'message' => 'Record already deleted or not found'
                ];
            }

            $record->delete();

            return [
                'success' => true,
                'server_record_id' => $record->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get model class for table name
     */
    private function getModelClass(string $tableName): ?string
    {
        $modelMap = [
            'patients' => Patient::class,
            // Add more model mappings as needed
        ];

        return $modelMap[$tableName] ?? null;
    }

    /**
     * Check if there's a conflict between existing and new data
     */
    private function hasConflict(SyncRecord $existingRecord, array $newData): bool
    {
        // If existing record is failed, no conflict
        if ($existingRecord->status === SyncRecord::STATUS_FAILED) {
            return false;
        }

        // Compare timestamps
        $existingTimestamp = $existingRecord->client_timestamp;
        $newTimestamp = isset($newData['client_timestamp']) 
            ? Carbon::parse($newData['client_timestamp']) 
            : now();

        // If new data is older than existing, it's a conflict
        if ($newTimestamp->lessThan($existingTimestamp)) {
            return true;
        }

        // Compare data hashes
        $newHash = $this->generateSyncHash($newData);
        if ($existingRecord->sync_hash && $existingRecord->sync_hash !== $newHash) {
            return true;
        }

        return false;
    }

    /**
     * Handle sync conflict
     */
    private function handleConflict(SyncRecord $existingRecord, array $newData, int $userId, int $enterpriseId, string $deviceId): array
    {
        // Mark as conflict
        $existingRecord->update(['status' => SyncRecord::STATUS_CONFLICT]);

        // Create conflict record for new data
        $conflictRecord = new SyncRecord();
        $conflictRecord->fill([
            'user_id' => $userId,
            'enterprise_id' => $enterpriseId,
            'device_id' => $deviceId,
            'table_name' => $newData['table_name'],
            'record_id' => $newData['record_id'],
            'operation' => $newData['operation'],
            'data' => $newData['data'] ?? null,
            'metadata' => $newData['metadata'] ?? null,
            'status' => SyncRecord::STATUS_CONFLICT,
            'client_timestamp' => isset($newData['client_timestamp']) 
                ? Carbon::parse($newData['client_timestamp']) 
                : now(),
            'sync_hash' => $this->generateSyncHash($newData)
        ]);
        $conflictRecord->save();

        return [
            'status' => 'conflict',
            'message' => 'Data conflict detected',
            'existing_record' => $existingRecord->toArray(),
            'conflict_record' => $conflictRecord->toArray(),
            'data' => $newData
        ];
    }

    /**
     * Generate sync hash for data integrity
     */
    private function generateSyncHash(array $data): string
    {
        $hashData = [
            'table_name' => $data['table_name'] ?? '',
            'operation' => $data['operation'] ?? '',
            'data' => $data['data'] ?? []
        ];

        return hash('sha256', json_encode($hashData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Get pending sync data for device
     */
    public function getPendingSyncData(int $userId, int $enterpriseId, string $deviceId, Carbon $lastSyncTime = null): array
    {
        $query = SyncRecord::where('user_id', $userId)
            ->where('enterprise_id', $enterpriseId)
            ->where('device_id', '!=', $deviceId) // Exclude current device
            ->whereIn('status', [SyncRecord::STATUS_SYNCED]);

        if ($lastSyncTime) {
            $query->where('server_timestamp', '>', $lastSyncTime);
        }

        $records = $query->orderBy('server_timestamp')
            ->limit(100) // Limit for performance
            ->get();

        return $records->map(function ($record) {
            return [
                'id' => $record->id,
                'table_name' => $record->table_name,
                'record_id' => $record->record_id,
                'operation' => $record->operation,
                'data' => $record->data,
                'metadata' => $record->metadata,
                'server_timestamp' => $record->server_timestamp->toISOString(),
                'sync_hash' => $record->sync_hash
            ];
        })->toArray();
    }

    /**
     * Queue sync operation for background processing
     */
    public function queueSyncOperation(int $userId, int $enterpriseId, string $queueName, array $payload, string $priority = SyncQueue::PRIORITY_MEDIUM): SyncQueue
    {
        return SyncQueue::create([
            'user_id' => $userId,
            'enterprise_id' => $enterpriseId,
            'queue_name' => $queueName,
            'payload' => $payload,
            'priority' => $priority,
            'status' => SyncQueue::STATUS_PENDING,
            'attempts' => 0
        ]);
    }

    /**
     * Process pending sync queue items
     */
    public function processSyncQueue(int $limit = 10): array
    {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $queueItems = SyncQueue::where('status', SyncQueue::STATUS_PENDING)
            ->where('available_at', '<=', now())
            ->orderBy('priority', 'desc')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        foreach ($queueItems as $item) {
            $results['processed']++;

            try {
                $item->update(['status' => SyncQueue::STATUS_PROCESSING]);

                // Process the queue item
                $success = $this->processQueueItem($item);

                if ($success) {
                    $item->update([
                        'status' => SyncQueue::STATUS_COMPLETED,
                        'completed_at' => now()
                    ]);
                    $results['successful']++;
                } else {
                    $this->handleQueueItemFailure($item);
                    $results['failed']++;
                }

            } catch (\Exception $e) {
                $this->handleQueueItemFailure($item, $e->getMessage());
                $results['failed']++;
                $results['errors'][] = [
                    'queue_id' => $item->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Process individual queue item
     */
    private function processQueueItem(SyncQueue $item): bool
    {
        $payload = $item->payload;

        switch ($item->queue_name) {
            case 'offline_sync':
                return $this->processOfflineSyncQueue($payload, $item);
                
            case 'data_sync':
                return $this->processDataSyncQueue($payload, $item);
                
            default:
                Log::warning('Unknown queue type', ['queue_name' => $item->queue_name]);
                return false;
        }
    }

    /**
     * Process offline sync queue item
     */
    private function processOfflineSyncQueue(array $payload, SyncQueue $item): bool
    {
        try {
            $this->syncFromDevice(
                $payload['offline_data'],
                $item->user_id,
                $item->enterprise_id,
                $payload['device_id']
            );
            return true;
        } catch (\Exception $e) {
            Log::error('Offline sync queue processing failed', [
                'queue_id' => $item->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Process data sync queue item
     */
    private function processDataSyncQueue(array $payload, SyncQueue $item): bool
    {
        // Implement specific data sync logic here
        return true;
    }

    /**
     * Handle queue item failure
     */
    private function handleQueueItemFailure(SyncQueue $item, string $errorMessage = null): void
    {
        $item->increment('attempts');

        if ($item->attempts >= $item->max_attempts) {
            $item->update([
                'status' => SyncQueue::STATUS_FAILED,
                'error_message' => $errorMessage ?? 'Max attempts reached'
            ]);
        } else {
            // Schedule retry with exponential backoff
            $retryDelay = pow(2, $item->attempts) * 60; // Minutes
            $item->update([
                'status' => SyncQueue::STATUS_PENDING,
                'available_at' => now()->addMinutes($retryDelay),
                'error_message' => $errorMessage
            ]);
        }
    }

    /**
     * Get sync statistics
     */
    public function getSyncStatistics(int $userId, int $enterpriseId, Carbon $since = null): array
    {
        $query = SyncRecord::where('user_id', $userId)
            ->where('enterprise_id', $enterpriseId);

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        $stats = $query->selectRaw('
            status,
            COUNT(*) as count,
            MAX(created_at) as last_sync
        ')
        ->groupBy('status')
        ->get()
        ->keyBy('status');

        return [
            'total' => $stats->sum('count'),
            'synced' => $stats->get(SyncRecord::STATUS_SYNCED)->count ?? 0,
            'pending' => $stats->get(SyncRecord::STATUS_PENDING)->count ?? 0,
            'failed' => $stats->get(SyncRecord::STATUS_FAILED)->count ?? 0,
            'conflicts' => $stats->get(SyncRecord::STATUS_CONFLICT)->count ?? 0,
            'last_sync' => $stats->max('last_sync')
        ];
    }
}
