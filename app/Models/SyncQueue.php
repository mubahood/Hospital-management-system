<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncQueue extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'user_id',
        'enterprise_id',
        'device_id',
        'queue_name',
        'payload',
        'priority',
        'status',
        'attempts',
        'max_attempts',
        'available_at',
        'processed_at',
        'error_message',
        'dependencies'
    ];

    protected $casts = [
        'payload' => 'array',
        'dependencies' => 'array',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'available_at' => 'datetime',
        'processed_at' => 'datetime'
    ];

    // Operation types
    const OPERATION_SYNC_UP = 'sync_up';
    const OPERATION_SYNC_DOWN = 'sync_down';
    const OPERATION_RESOLVE_CONFLICT = 'resolve_conflict';

    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    // Status types
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Get the user that owns the sync queue item
     */
    public function user()
    {
        return $this->belongsTo(Administrator::class, 'user_id');
    }

    /**
     * Scope to pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to processing items
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope to failed items
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to high priority items
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_CRITICAL]);
    }

    /**
     * Scope to ready for processing (no dependencies or dependencies met)
     */
    public function scopeReadyForProcessing($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('available_at', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('dependencies')
                          ->orWhereJsonLength('dependencies', 0);
                    });
    }

    /**
     * Scope by priority order
     */
    public function scopeByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')");
    }

    /**
     * Scope to specific device
     */
    public function scopeForDevice($query, $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing()
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processed_at' => now()
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now()
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($error = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
            'attempts' => $this->attempts + 1,
            'processed_at' => now()
        ]);
    }

    /**
     * Check if can retry
     */
    public function canRetry()
    {
        return $this->attempts < $this->max_attempts;
    }

    /**
     * Reset for retry
     */
    public function resetForRetry($delayMinutes = 5)
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'available_at' => now()->addMinutes($delayMinutes),
            'error_message' => null
        ]);
    }

    /**
     * Check if dependencies are met
     */
    public function dependenciesMet()
    {
        if (empty($this->dependencies)) {
            return true;
        }

        foreach ($this->dependencies as $dependency) {
            $exists = self::where('id', $dependency)
                         ->where('status', self::STATUS_COMPLETED)
                         ->exists();
            
            if (!$exists) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get priority weight for sorting
     */
    public function getPriorityWeight()
    {
        return match($this->priority) {
            self::PRIORITY_CRITICAL => 4,
            self::PRIORITY_HIGH => 3,
            self::PRIORITY_MEDIUM => 2,
            self::PRIORITY_LOW => 1,
            default => 0
        };
    }
}
