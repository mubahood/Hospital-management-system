<?php

namespace App\Traits;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

trait EnterpriseScopeTrait
{
    /**
     * Boot the trait
     */
    protected static function bootEnterpriseScopeTrait()
    {
        // Auto-scope queries to current enterprise
        static::addGlobalScope('enterprise', function (Builder $builder) {
            $user = Admin::user();
            if ($user && $user->enterprise_id) {
                $builder->where('enterprise_id', $user->enterprise_id);
            }
        });

        // Auto-set enterprise_id when creating records
        static::creating(function ($model) {
            if (!$model->enterprise_id) {
                $user = Admin::user();
                if ($user && $user->enterprise_id) {
                    $model->enterprise_id = $user->enterprise_id;
                }
            }
        });
    }

    /**
     * Get the enterprise relationship
     */
    public function enterprise()
    {
        return $this->belongsTo(\App\Models\Enterprise::class, 'enterprise_id');
    }

    /**
     * Scope query to specific enterprise
     */
    public function scopeForEnterprise($query, $enterpriseId)
    {
        return $query->where('enterprise_id', $enterpriseId);
    }
}
