<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncRecord extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'user_id',
        'enterprise_id',
        'device_id',
        'table_name',
        'record_id',
        'operation',
        'status',
        'data',
        'sync_hash',
        'conflict_resolution',
        'client_timestamp',
        'server_timestamp',
        'retry_count',
        'last_error',
        'dependencies'
    ];

    protected $casts = [
        'data' => 'array',
        'dependencies' => 'array',
        'client_timestamp' => 'datetime',
        'server_timestamp' => 'datetime',
        'retry_count' => 'integer'
    ];

    // Operation types
    const OPERATION_CREATE = 'create';
    const OPERATION_UPDATE = 'update';
    const OPERATION_DELETE = 'delete';

    // Status types
    const STATUS_PENDING = 'pending';
    const STATUS_SYNCED = 'synced';
    const STATUS_FAILED = 'failed';
    const STATUS_CONFLICT = 'conflict';

    /**
     * Get the user that owns the sync record
     */
    public function user()
    {
        return $this->belongsTo(Administrator::class, 'user_id');
    }

    /**
     * Scope to pending records
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to failed records
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to conflict records
     */
    public function scopeConflict($query)
    {
        return $query->where('status', self::STATUS_CONFLICT);
    }

    /**
     * Scope to specific device
     */
    public function scopeForDevice($query, $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Scope to specific table
     */
    public function scopeForTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Mark as synced
     */
    public function markAsSynced()
    {
        $this->update([
            'status' => self::STATUS_SYNCED,
            'server_timestamp' => now()
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($error = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'last_error' => $error,
            'retry_count' => $this->retry_count + 1
        ]);
    }

    /**
     * Mark as conflict
     */
    public function markAsConflict($resolution = null)
    {
        $this->update([
            'status' => self::STATUS_CONFLICT,
            'conflict_resolution' => $resolution
        ]);
    }

    /**
     * Check if can retry
     */
    public function canRetry($maxRetries = 3)
    {
        return $this->retry_count < $maxRetries;
    }

    /**
     * Generate sync hash for conflict detection
     */
    public static function generateSyncHash($data)
    {
        return md5(serialize($data));
    }
}
