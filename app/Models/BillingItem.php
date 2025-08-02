<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BillingItem extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'consultation_id',
        'type',
        'description',
        'price',
        'quantity',
        'total_amount',
        'discount',
        'status',
        'created_by',
        'approved_by',
        'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes for filtering billing items
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByConsultation($query, $consultationId)
    {
        return $query->where('consultation_id', $consultationId);
    }

    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('type', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                     ->whereYear('created_at', Carbon::now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Relationships
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function patient()
    {
        return $this->hasOneThrough(User::class, Consultation::class, 'id', 'id', 'consultation_id', 'patient_id');
    }

    public function enterprise()
    {
        return $this->belongsTo(Company::class, 'enterprise_id');
    }

    // Accessor Methods
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    public function getBillingNumberAttribute()
    {
        return 'BI' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d M Y, H:i');
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getShortDescriptionAttribute()
    {
        return strlen($this->description) > 50 
            ? substr($this->description, 0, 50) . '...'
            : $this->description;
    }

    // Helper Methods
    public function isExpensive($threshold = 1000)
    {
        return $this->price > $threshold;
    }

    public function isCheap($threshold = 100)
    {
        return $this->price < $threshold;
    }

    // Static Helper Methods
    public static function getBillingTypes()
    {
        return [
            'consultation' => 'Consultation Fee',
            'laboratory' => 'Laboratory Test',
            'radiology' => 'Radiology/Imaging',
            'pharmacy' => 'Pharmacy',
            'procedure' => 'Medical Procedure',
            'surgery' => 'Surgery',
            'admission' => 'Admission Fee',
            'bed_charge' => 'Bed Charge',
            'nursing' => 'Nursing Care',
            'physiotherapy' => 'Physiotherapy',
            'specialist' => 'Specialist Consultation',
            'emergency' => 'Emergency Service',
            'ambulance' => 'Ambulance Service',
            'other' => 'Other',
        ];
    }

    public static function getTotalBillingByType($type, $dateRange = null)
    {
        $query = static::byType($type);
        
        if ($dateRange) {
            $query->dateRange($dateRange['start'], $dateRange['end']);
        }
        
        return $query->sum('price');
    }

    public static function getTotalBillingForConsultation($consultationId)
    {
        return static::byConsultation($consultationId)->sum('price');
    }

    public static function getTopBillingTypes($limit = 5)
    {
        return static::selectRaw('type, SUM(price) as total_amount, COUNT(*) as count')
                     ->groupBy('type')
                     ->orderBy('total_amount', 'desc')
                     ->limit($limit)
                     ->get();
    }

    public static function getBillingStatistics($dateRange = null)
    {
        $query = static::query();
        
        if ($dateRange) {
            $query->dateRange($dateRange['start'], $dateRange['end']);
        }
        
        return [
            'total_amount' => $query->sum('price'),
            'total_items' => $query->count(),
            'average_amount' => $query->avg('price'),
            'highest_amount' => $query->max('price'),
            'lowest_amount' => $query->min('price'),
        ];
    }

    public static function searchBillingItems($term)
    {
        return static::search($term)
                     ->with(['consultation', 'consultation.patient'])
                     ->orderBy('created_at', 'desc')
                     ->get();
    }
}
