<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'user_id',
        'enterprise_id',
        'device_token',
        'device_type',
        'platform',
        'app_version',
        'device_model',
        'os_version',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the device token
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the enterprise that owns the device token
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Scope to get active tokens only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tokens by platform
     */
    public function scopePlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope to get tokens by device type
     */
    public function scopeDeviceType($query, $type)
    {
        return $query->where('device_type', $type);
    }

    /**
     * Mark token as used
     */
    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Deactivate the token
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}
