<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MedicalService extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'consultation_id',
        'receptionist_id',
        'patient_id',
        'assigned_to_id',
        'type',
        'status',
        'remarks',
        'instruction',
        'specialist_outcome',
        'file',
        'description',
        'total_price',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes for filtering medical services
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByConsultation($query, $consultationId)
    {
        return $query->where('consultation_id', $consultationId);
    }

    public function scopeAssignedTo($query, $assignedToId)
    {
        return $query->where('assigned_to_id', $assignedToId);
    }

    public function scopeByReceptionist($query, $receptionistId)
    {
        return $query->where('receptionist_id', $receptionistId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                     ->whereYear('created_at', Carbon::now()->year);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('type', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('remarks', 'like', "%{$term}%")
              ->orWhere('instruction', 'like', "%{$term}%")
              ->orWhere('specialist_outcome', 'like', "%{$term}%");
        });
    }

    public function scopeWithFile($query)
    {
        return $query->whereNotNull('file');
    }

    public function scopeWithoutFile($query)
    {
        return $query->whereNull('file');
    }

    public function scopePriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('total_price', [$minPrice, $maxPrice]);
    }

    // Relationships


    // Relationships
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function receptionist()
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function medicalServiceItems()
    {
        return $this->hasMany(MedicalServiceItem::class);
    }

    public function enterprise()
    {
        return $this->belongsTo(Company::class, 'enterprise_id');
    }

    // Accessor Methods
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 2);
    }

    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 2);
    }

    public function getCalculatedTotalAttribute()
    {
        return $this->unit_price * $this->quantity;
    }

    public function getServiceNumberAttribute()
    {
        return 'MS' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d M Y, H:i');
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getHasFileAttribute()
    {
        return !empty($this->file);
    }

    // Helper Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeModified()
    {
        return in_array($this->status, ['pending', 'in_progress']);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'in_progress']);
    }

    public function hasItems()
    {
        return $this->medicalServiceItems()->count() > 0;
    }

    public function getTotalItemsCount()
    {
        return $this->medicalServiceItems()->count();
    }

    public function getItemsTotalPrice()
    {
        return $this->medicalServiceItems()->sum('total_price') ?? 0;
    }

    public function updateTotalPrice()
    {
        $itemsTotal = $this->getItemsTotalPrice();
        $this->update(['total_price' => $itemsTotal]);
        return $itemsTotal;
    }

    public function markAsCompleted($specialistOutcome = null)
    {
        $this->update([
            'status' => 'completed',
            'specialist_outcome' => $specialistOutcome ?? $this->specialist_outcome,
        ]);
    }

    public function markAsInProgress()
    {
        $this->update(['status' => 'in_progress']);
    }

    public function markAsCancelled($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'remarks' => $reason ? $this->remarks . ' | Cancelled: ' . $reason : $this->remarks,
        ]);
    }

    // Static Helper Methods
    public static function getServiceTypes()
    {
        return [
            'laboratory' => 'Laboratory Test',
            'radiology' => 'Radiology/Imaging',
            'pharmacy' => 'Pharmacy',
            'physiotherapy' => 'Physiotherapy',
            'nursing' => 'Nursing Care',
            'specialist' => 'Specialist Consultation',
            'surgery' => 'Surgery',
            'procedure' => 'Medical Procedure',
            'other' => 'Other',
        ];
    }

    public static function getStatuses()
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function getServicesForSelect()
    {
        return static::select('id', 'type', 'description', 'total_price')
                     ->get()
                     ->mapWithKeys(function ($service) {
                         return [$service->id => $service->type . ' - ' . $service->description . ' (' . $service->formatted_total_price . ')'];
                     })
                     ->toArray();
    }

    public static function searchServices($term)
    {
        return static::search($term)
                     ->with(['consultation', 'patient', 'assigned_to'])
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    // Legacy method for backwards compatibility
    public function items_text()
    {
        $items = $this->medicalServiceItems;
        $text = '';
        $isFirst = true;
        foreach ($items as $item) {
            if ($isFirst) {
                $text .= $item->remarks;
                $isFirst = false;
            } else {
                $text .= ', ' . $item->remarks;
            }
        }
        return $text . '.';
    }
}
