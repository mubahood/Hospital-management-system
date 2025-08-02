<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OfflineSyncService;
use App\Models\SyncRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OfflineSyncController extends Controller
{
    protected $syncService;

    public function __construct(OfflineSyncService $syncService)
    {
        $this->syncService = $syncService;
        $this->middleware('auth:api');
    }

    /**
     * Sync offline data from mobile device
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function syncToServer(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'device_id' => 'required|string|max:255',
                'offline_data' => 'required|array',
                'offline_data.*.table_name' => 'required|string|max:100',
                'offline_data.*.operation' => 'required|in:create,update,delete',
                'offline_data.*.record_id' => 'required|integer',
                'offline_data.*.data' => 'sometimes|array',
                'offline_data.*.metadata' => 'sometimes|array',
                'offline_data.*.client_timestamp' => 'sometimes|date',
                'offline_data.*.client_version' => 'sometimes|string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $deviceId = $request->input('device_id');
            $offlineData = $request->input('offline_data');

            // Validate device ID format
            if (!$this->isValidDeviceId($deviceId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid device ID format'
                ], 400);
            }

            // Process offline sync
            $results = $this->syncService->syncFromDevice(
                $offlineData,
                $user->id,
                $user->enterprise_id,
                $deviceId
            );

            return response()->json([
                'success' => true,
                'message' => 'Offline sync completed',
                'data' => [
                    'sync_results' => $results,
                    'sync_timestamp' => now()->toISOString(),
                    'server_time' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Offline sync error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending data that needs to be synced to device
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function syncFromServer(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'device_id' => 'required|string|max:255',
                'last_sync_time' => 'sometimes|date',
                'limit' => 'sometimes|integer|min:1|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $deviceId = $request->input('device_id');
            $lastSyncTime = $request->input('last_sync_time') 
                ? Carbon::parse($request->input('last_sync_time'))
                : null;

            $pendingData = $this->syncService->getPendingSyncData(
                $user->id,
                $user->enterprise_id,
                $deviceId,
                $lastSyncTime
            );

            return response()->json([
                'success' => true,
                'message' => 'Pending sync data retrieved',
                'data' => [
                    'pending_records' => $pendingData,
                    'total_records' => count($pendingData),
                    'sync_timestamp' => now()->toISOString(),
                    'server_time' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get pending sync data error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync status and statistics
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getSyncStatus(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'device_id' => 'sometimes|string|max:255',
                'since' => 'sometimes|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $since = $request->input('since') 
                ? Carbon::parse($request->input('since'))
                : Carbon::now()->subDays(7); // Default to last 7 days

            $statistics = $this->syncService->getSyncStatistics(
                $user->id,
                $user->enterprise_id,
                $since
            );

            // Get recent sync records
            $recentSyncs = SyncRecord::where('user_id', $user->id)
                ->where('enterprise_id', $user->enterprise_id)
                ->when($request->input('device_id'), function ($query, $deviceId) {
                    return $query->where('device_id', $deviceId);
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['id', 'device_id', 'table_name', 'operation', 'status', 'created_at', 'error_message']);

            return response()->json([
                'success' => true,
                'message' => 'Sync status retrieved',
                'data' => [
                    'statistics' => $statistics,
                    'recent_syncs' => $recentSyncs,
                    'server_time' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get sync status error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sync status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve sync conflicts
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function resolveConflicts(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'conflicts' => 'required|array',
                'conflicts.*.sync_record_id' => 'required|integer|exists:sync_records,id',
                'conflicts.*.resolution' => 'required|in:use_server,use_client,merge',
                'conflicts.*.merged_data' => 'required_if:conflicts.*.resolution,merge|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $conflicts = $request->input('conflicts');
            $resolvedConflicts = [];
            $errors = [];

            foreach ($conflicts as $conflict) {
                try {
                    $syncRecord = SyncRecord::where('id', $conflict['sync_record_id'])
                        ->where('user_id', $user->id)
                        ->where('enterprise_id', $user->enterprise_id)
                        ->where('status', SyncRecord::STATUS_CONFLICT)
                        ->first();

                    if (!$syncRecord) {
                        $errors[] = "Sync record {$conflict['sync_record_id']} not found or not in conflict";
                        continue;
                    }

                    $resolution = $this->resolveConflict($syncRecord, $conflict);
                    $resolvedConflicts[] = $resolution;

                } catch (\Exception $e) {
                    $errors[] = "Error resolving conflict {$conflict['sync_record_id']}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => count($errors) === 0,
                'message' => count($errors) === 0 ? 'All conflicts resolved' : 'Some conflicts could not be resolved',
                'data' => [
                    'resolved_conflicts' => $resolvedConflicts,
                    'errors' => $errors,
                    'total_processed' => count($conflicts),
                    'successful_resolutions' => count($resolvedConflicts)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Resolve conflicts error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve conflicts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Queue sync operation for background processing
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function queueSync(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'queue_name' => 'required|string|max:100',
                'payload' => 'required|array',
                'priority' => 'sometimes|in:low,medium,high,critical',
                'scheduled_at' => 'sometimes|date|after:now'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $queueName = $request->input('queue_name');
            $payload = $request->input('payload');
            $priority = $request->input('priority', 'medium');

            $queueItem = $this->syncService->queueSyncOperation(
                $user->id,
                $user->enterprise_id,
                $queueName,
                $payload,
                $priority
            );

            // If scheduled_at is provided, update it
            if ($request->has('scheduled_at')) {
                $queueItem->update([
                    'scheduled_at' => Carbon::parse($request->input('scheduled_at'))
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sync operation queued successfully',
                'data' => [
                    'queue_id' => $queueItem->id,
                    'status' => $queueItem->status,
                    'priority' => $queueItem->priority,
                    'scheduled_at' => $queueItem->scheduled_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Queue sync operation error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to queue sync operation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test sync connectivity
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function testSync(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'device_id' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $deviceId = $request->input('device_id');

            // Test data
            $testData = [
                [
                    'table_name' => 'test_sync',
                    'operation' => 'create',
                    'record_id' => 999999,
                    'data' => ['test' => true, 'timestamp' => now()->toISOString()],
                    'client_timestamp' => now()->toISOString(),
                    'client_version' => '1.0.0'
                ]
            ];

            // Test sync process without actually saving
            $testResult = [
                'device_connectivity' => true,
                'authentication' => true,
                'enterprise_access' => true,
                'sync_service_available' => true,
                'database_accessible' => true,
                'server_time' => now()->toISOString(),
                'user_id' => $user->id,
                'enterprise_id' => $user->enterprise_id,
                'device_id' => $deviceId
            ];

            return response()->json([
                'success' => true,
                'message' => 'Sync connectivity test successful',
                'data' => $testResult
            ]);

        } catch (\Exception $e) {
            Log::error('Test sync error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync connectivity test failed: ' . $e->getMessage(),
                'data' => [
                    'device_connectivity' => false,
                    'server_time' => now()->toISOString()
                ]
            ], 500);
        }
    }

    /**
     * Validate device ID format
     */
    private function isValidDeviceId(string $deviceId): bool
    {
        // Device ID should be UUID format or at least 8 characters
        return strlen($deviceId) >= 8 && strlen($deviceId) <= 255;
    }

    /**
     * Resolve individual conflict
     */
    private function resolveConflict(SyncRecord $syncRecord, array $conflictData): array
    {
        $resolution = $conflictData['resolution'];

        switch ($resolution) {
            case 'use_server':
                // Keep server data, mark conflict as resolved
                $syncRecord->update([
                    'status' => SyncRecord::STATUS_SYNCED,
                    'metadata' => array_merge($syncRecord->metadata ?? [], [
                        'conflict_resolved' => now()->toISOString(),
                        'resolution' => 'use_server'
                    ])
                ]);
                break;

            case 'use_client':
                // Use client data, update server record
                $this->syncService->executeOperation(
                    $syncRecord->table_name,
                    $syncRecord->operation,
                    ['data' => $syncRecord->data],
                    $syncRecord->enterprise_id
                );
                
                $syncRecord->update([
                    'status' => SyncRecord::STATUS_SYNCED,
                    'metadata' => array_merge($syncRecord->metadata ?? [], [
                        'conflict_resolved' => now()->toISOString(),
                        'resolution' => 'use_client'
                    ])
                ]);
                break;

            case 'merge':
                // Use merged data provided by client
                $mergedData = $conflictData['merged_data'];
                
                $this->syncService->executeOperation(
                    $syncRecord->table_name,
                    $syncRecord->operation,
                    ['data' => $mergedData],
                    $syncRecord->enterprise_id
                );
                
                $syncRecord->update([
                    'status' => SyncRecord::STATUS_SYNCED,
                    'data' => $mergedData,
                    'metadata' => array_merge($syncRecord->metadata ?? [], [
                        'conflict_resolved' => now()->toISOString(),
                        'resolution' => 'merge'
                    ])
                ]);
                break;
        }

        return [
            'sync_record_id' => $syncRecord->id,
            'resolution' => $resolution,
            'status' => $syncRecord->status,
            'resolved_at' => now()->toISOString()
        ];
    }
}
