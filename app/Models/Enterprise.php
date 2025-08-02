<?php

namespace App\Models;

use App\Traits\StandardBootTrait;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterprise extends Model
{
    use HasFactory;
    use StandardBootTrait;

    protected $fillable = [
        'name',
        'short_name',
        'details',
        'logo',
        'phone_number',
        'email',
        'address',
        'expiry',
        'administrator_id',
        'subdomain',
        'color',
        'welcome_message',
        'type',
        'phone_number_2',
        'p_o_box',
        'hm_signature',
        'dos_signature',
        'bursar_signature',
        'dp_year',
        'school_pay_code',
        'school_pay_password',
        'has_theology',
        'dp_term_id',
        'motto',
        'website',
        'hm_name',
        'wallet_balance',
        'can_send_messages',
        'has_valid_lisence',
        'school_pay_status',
        'sec_color',
        'school_pay_import_automatically',
        'school_pay_last_accepted_date',
        'status',
        'timezone',
        'currency',
        'language',
        'max_users',
        'storage_limit'
    ];

    protected $casts = [
        'expiry' => 'datetime',
        'wallet_balance' => 'decimal:2',
        'max_users' => 'integer',
        'storage_limit' => 'integer',
        'has_theology' => 'boolean',
        'can_send_messages' => 'boolean',
        'has_valid_lisence' => 'boolean',
        'school_pay_import_automatically' => 'boolean',
    ];

    /**
     * Boot the model with standardized event handling.
     */
    protected static function boot(): void
    {
        parent::boot();
    }

    /**
     * Override setEnterpriseIdIfNeeded to prevent self-referencing.
     * Enterprise model should not set enterprise_id on itself.
     */
    protected function setEnterpriseIdIfNeeded(): void
    {
        // Do nothing - Enterprise model doesn't need enterprise_id
    }

    /**
     * Custom logic after creating an enterprise.
     */
    protected function processCustomAfterCreate(): void
    {
        $this->assignAdministratorToEnterprise();
    }

    /**
     * Custom logic after updating an enterprise.
     */
    protected function processCustomAfterUpdate(): void
    {
        $this->assignAdministratorToEnterprise();
    }

    /**
     * Assign administrator to this enterprise.
     */
    private function assignAdministratorToEnterprise(): void
    {
        $administrator = Administrator::find($this->administrator_id);
        if ($administrator) {
            $administrator->enterprise_id = $this->id;
            $administrator->save();
        }
    }

    // Relationships
    public function administrator()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'enterprise_id');
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'enterprise_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'enterprise_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'enterprise_id');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'enterprise_id');
    }

    /**
     * Get all medical services for this enterprise
     */
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class, 'enterprise_id');
    }

    /**
     * Get all billing items for this enterprise
     */
    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'enterprise_id');
    }

    /**
     * Get all payment records for this enterprise
     */
    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class, 'enterprise_id');
    }

    /**
     * Get all dose items for this enterprise
     */
    public function doseItems()
    {
        return $this->hasMany(DoseItem::class, 'enterprise_id');
    }

    /**
     * Get all tasks for this enterprise
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'enterprise_id');
    }

    /**
     * Get all events for this enterprise
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'enterprise_id');
    }

    /**
     * Get all meetings for this enterprise
     */
    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'enterprise_id');
    }

    // Helper methods
    public function getNameTextAttribute()
    {
        return $this->name . ' (' . $this->short_name . ')';
    }

    protected $appends = ['name_text'];
}
