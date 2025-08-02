<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait StandardBootTrait
 * 
 * Provides standardized boot functionality for models including:
 * - UUID generation for models that need it
 * - Enterprise scoping integration
 * - Standard model event handling
 * - Automatic field processing
 */
trait StandardBootTrait
{
    /**
     * Initialize the boot trait.
     */
    protected static function bootStandardBootTrait(): void
    {
        static::creating(function ($model) {
            $model->processBeforeCreate();
        });

        static::updating(function ($model) {
            $model->processBeforeUpdate();
        });

        static::saving(function ($model) {
            $model->processBeforeSave();
        });

        static::created(function ($model) {
            $model->processAfterCreate();
        });

        static::updated(function ($model) {
            $model->processAfterUpdate();
        });

        static::saved(function ($model) {
            $model->processAfterSave();
        });

        static::deleting(function ($model) {
            $model->processBeforeDelete();
        });

        static::deleted(function ($model) {
            $model->processAfterDelete();
        });
    }

    /**
     * Process model before creation.
     * Override in individual models for custom logic.
     */
    protected function processBeforeCreate(): void
    {
        $this->generateUuidIfNeeded();
        $this->setEnterpriseIdIfNeeded();
        $this->cleanEmailField();
        $this->processCustomBeforeCreate();
    }

    /**
     * Process model before update.
     * Override in individual models for custom logic.
     */
    protected function processBeforeUpdate(): void
    {
        $this->cleanEmailField();
        $this->processCustomBeforeUpdate();
    }

    /**
     * Process model before save (both create and update).
     * Override in individual models for custom logic.
     */
    protected function processBeforeSave(): void
    {
        $this->processCustomBeforeSave();
    }

    /**
     * Process model after creation.
     * Override in individual models for custom logic.
     */
    protected function processAfterCreate(): void
    {
        $this->processCustomAfterCreate();
    }

    /**
     * Process model after update.
     * Override in individual models for custom logic.
     */
    protected function processAfterUpdate(): void
    {
        $this->processCustomAfterUpdate();
    }

    /**
     * Process model after save (both create and update).
     * Override in individual models for custom logic.
     */
    protected function processAfterSave(): void
    {
        $this->processCustomAfterSave();
    }

    /**
     * Process model before deletion.
     * Override in individual models for custom logic.
     */
    protected function processBeforeDelete(): void
    {
        $this->processCustomBeforeDelete();
    }

    /**
     * Process model after deletion.
     * Override in individual models for custom logic.
     */
    protected function processAfterDelete(): void
    {
        $this->processCustomAfterDelete();
    }

    /**
     * Generate UUID if the model has a uuid field and it's empty.
     */
    protected function generateUuidIfNeeded(): void
    {
        if ($this->hasUuidField() && empty($this->getAttribute($this->getUuidField()))) {
            $this->setAttribute($this->getUuidField(), Str::uuid()->toString());
        }
    }

    /**
     * Set enterprise_id if needed and available.
     */
    protected function setEnterpriseIdIfNeeded(): void
    {
        if ($this->hasEnterpriseField() && empty($this->getAttribute('enterprise_id'))) {
            $enterpriseId = $this->getDefaultEnterpriseId();
            if ($enterpriseId) {
                $this->setAttribute('enterprise_id', $enterpriseId);
            }
        }
    }

    /**
     * Clean email field if present.
     */
    protected function cleanEmailField(): void
    {
        if ($this->hasEmailField()) {
            $email = $this->getAttribute('email');
            if (!empty($email)) {
                $this->setAttribute('email', trim(strtolower($email)));
            }
        }
    }

    /**
     * Check if model has UUID field.
     */
    protected function hasUuidField(): bool
    {
        return in_array($this->getUuidField(), $this->getFillable()) || 
               in_array($this->getUuidField(), $this->getGuarded());
    }

    /**
     * Get the UUID field name.
     */
    protected function getUuidField(): string
    {
        return property_exists($this, 'uuidField') ? $this->uuidField : 'uuid';
    }

    /**
     * Check if model has enterprise field.
     */
    protected function hasEnterpriseField(): bool
    {
        return in_array('enterprise_id', $this->getFillable()) ||
               (is_array($this->getGuarded()) && !in_array('enterprise_id', $this->getGuarded()));
    }

    /**
     * Check if model has email field.
     */
    protected function hasEmailField(): bool
    {
        return in_array('email', $this->getFillable()) ||
               (is_array($this->getGuarded()) && !in_array('email', $this->getGuarded()));
    }

    /**
     * Get default enterprise ID.
     */
    protected function getDefaultEnterpriseId(): ?int
    {
        // Try to get from current user or request context
        if (auth()->check() && auth()->user()->enterprise_id) {
            return auth()->user()->enterprise_id;
        }

        // Fallback to default enterprise
        return 1;
    }

    // Hook methods for individual models to override
    protected function processCustomBeforeCreate(): void {}
    protected function processCustomBeforeUpdate(): void {}
    protected function processCustomBeforeSave(): void {}
    protected function processCustomAfterCreate(): void {}
    protected function processCustomAfterUpdate(): void {}
    protected function processCustomAfterSave(): void {}
    protected function processCustomBeforeDelete(): void {}
    protected function processCustomAfterDelete(): void {}
}
