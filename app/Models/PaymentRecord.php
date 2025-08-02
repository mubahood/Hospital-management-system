<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'consultation_id',
        'description',
        'amount_payable',
        'amount_paid',
        'balance',
        'payment_date',
        'payment_time',
        'payment_method',
        'payment_reference',
        'payment_status',
        'payment_remarks',
        'payment_phone_number',
        'payment_channel',
        'cash_received_by_id',
        'created_by_id',
        'cash_receipt_number',
        'card_id',
        'company_id',
        'card_number',
        'card_type',
        'flutterwave_reference',
        'flutterwave_payment_type',
        'flutterwave_payment_status',
        'flutterwave_payment_message',
        'flutterwave_payment_code',
        'flutterwave_payment_data',
        'flutterwave_payment_link',
        'flutterwave_payment_amount',
        'flutterwave_payment_customer_name',
        'flutterwave_payment_customer_id',
        'flutterwave_payment_customer_email',
        'flutterwave_payment_customer_phone_number',
        'flutterwave_payment_customer_full_name',
        'flutterwave_payment_customer_created_at',
    ];

    protected $casts = [
        'amount_payable' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'flutterwave_payment_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'payment_time' => 'datetime',
        'flutterwave_payment_customer_created_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    //boot
    protected static function boot(): void
    {
        parent::boot();
        
        // Call standardized boot methods
        static::bootStandardBootTrait();
    }

    /**
     * Handle model creation logic - called by StandardBootTrait
     */
    protected static function onCreating($model): void
    {
        $consultation = Consultation::find($model->consultation_id);
        if ($consultation == null) {
            throw new \Exception('Consultation not found.');
        }
        //main_status
        if ($consultation->main_status == 'Completed') {
            throw new \Exception('Consultation already completed.');
        }
        $model = PaymentRecord::prepare($model);
    }

    /**
     * Handle model updating logic - called by StandardBootTrait
     */
    protected static function onUpdating($model): void
    {
        $model = PaymentRecord::prepare($model);
        if (strtolower($model->payment_method) == 'card') {
            $model->payment_status = 'Success';
        }
    }

    /**
     * Handle post-creation logic - called by StandardBootTrait
     */
    protected static function onCreated($model): void
    {
        $consultation = Consultation::find($model->consultation_id);
        if ($consultation == null) {
            throw new \Exception('Consultation not found.');
        }
        $consultation->process_balance();

        //check if is card and create card record 
        if (strtolower($model->payment_method) == 'card') {
            $card = User::find($model->card_id);
            if ($card == null) {
                throw new \Exception('Card not found.');
            }
            $cardRecord = new CardRecord();
            $cardRecord->card_id = $model->card_id;
            $cardRecord->type = 'Debit';
            $cardRecord->amount = $model->amount_paid;
            $cardRecord->payment_date = $model->payment_date;
            $cardRecord->payment_remarks = '#' . $model->id . " - " . $model->description;
            $cardRecord->save();
        }
    }

    /**
     * Handle post-update logic - called by StandardBootTrait
     */
    protected static function onUpdated($model): void
    {
        $consultation = Consultation::find($model->consultation_id);
        if ($consultation == null) {
            throw new \Exception('Consultation not found.');
        }
        $consultation->process_balance();
    }

    /**
     * Handle pre-deletion logic - called by StandardBootTrait
     */
    protected static function onDeleting($model): void
    {
        $consultation = Consultation::find($model->consultation_id);
        if ($consultation == null) {
            throw new \Exception('Consultation not found.');
        }
        $consultation->process_balance();
    }

    // Scopes for filtering payment records
    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('payment_status', 'Success');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'Pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'Failed');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeCashPayments($query)
    {
        return $query->where('payment_method', 'Cash');
    }

    public function scopeCardPayments($query)
    {
        return $query->where('payment_method', 'Card');
    }

    public function scopeMobileMoneyPayments($query)
    {
        return $query->where('payment_method', 'Mobile Money');
    }

    public function scopeFlutterwavePayments($query)
    {
        return $query->where('payment_method', 'Flutterwave');
    }

    public function scopeByConsultation($query, $consultationId)
    {
        return $query->where('consultation_id', $consultationId);
    }

    public function scopeByAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('amount_paid', [$minAmount, $maxAmount]);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', Carbon::today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', Carbon::now()->month)
                     ->whereYear('payment_date', Carbon::now()->year);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('description', 'like', "%{$term}%")
              ->orWhere('payment_reference', 'like', "%{$term}%")
              ->orWhere('payment_remarks', 'like', "%{$term}%")
              ->orWhere('cash_receipt_number', 'like', "%{$term}%");
        });
    }

    public function scopePartialPayments($query)
    {
        return $query->where('balance', '>', 0);
    }

    public function scopeFullPayments($query)
    {
        return $query->where('balance', '<=', 0);
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

    public function cashReceiver()
    {
        return $this->belongsTo(User::class, 'cash_received_by_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function card()
    {
        return $this->belongsTo(User::class, 'card_id');
    }

    public function enterprise()
    {
        return $this->belongsTo(Company::class, 'enterprise_id');
    }

    // Accessor Methods
    public function getFormattedAmountPaidAttribute()
    {
        return number_format($this->amount_paid, 2);
    }

    public function getFormattedAmountPayableAttribute()
    {
        return number_format($this->amount_payable, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2);
    }

    public function getPaymentNumberAttribute()
    {
        return 'PR' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'Success' => 'success',
            'Pending' => 'warning',
            'Failed' => 'danger',
        ];

        return $badges[$this->payment_status] ?? 'secondary';
    }

    public function getMethodBadgeAttribute()
    {
        $badges = [
            'Cash' => 'success',
            'Card' => 'primary',
            'Mobile Money' => 'info',
            'Flutterwave' => 'warning',
        ];

        return $badges[$this->payment_method] ?? 'secondary';
    }

    public function getFormattedPaymentDateAttribute()
    {
        return $this->payment_date ? $this->payment_date->format('d M Y, H:i') : null;
    }

    public function getTimeAgoAttribute()
    {
        return $this->payment_date ? $this->payment_date->diffForHumans() : null;
    }

    public function getIsPartialPaymentAttribute()
    {
        return $this->balance > 0;
    }

    public function getIsFullPaymentAttribute()
    {
        return $this->balance <= 0;
    }

    public function getPaymentPercentageAttribute()
    {
        if ($this->amount_payable <= 0) return 0;
        return round(($this->amount_paid / $this->amount_payable) * 100, 2);
    }

    // Helper Methods
    public function isSuccessful()
    {
        return $this->payment_status === 'Success';
    }

    public function isPending()
    {
        return $this->payment_status === 'Pending';
    }

    public function isFailed()
    {
        return $this->payment_status === 'Failed';
    }

    public function isCashPayment()
    {
        return $this->payment_method === 'Cash';
    }

    public function isCardPayment()
    {
        return $this->payment_method === 'Card';
    }

    public function isMobileMoneyPayment()
    {
        return $this->payment_method === 'Mobile Money';
    }

    public function isFlutterwavePayment()
    {
        return $this->payment_method === 'Flutterwave';
    }

    public function isPartialPayment()
    {
        return $this->balance > 0;
    }

    public function isFullPayment()
    {
        return $this->balance <= 0;
    }

    public function canBeRefunded()
    {
        return $this->isSuccessful() && $this->amount_paid > 0;
    }

    public function markAsSuccessful($reference = null)
    {
        $this->update([
            'payment_status' => 'Success',
            'payment_reference' => $reference ?? $this->payment_reference,
        ]);
    }

    public function markAsFailed($reason = null)
    {
        $this->update([
            'payment_status' => 'Failed',
            'payment_remarks' => $reason ? $this->payment_remarks . ' | Failed: ' . $reason : $this->payment_remarks,
        ]);
    }

    // Static Helper Methods
    public static function getPaymentMethods()
    {
        return [
            'Cash' => 'Cash Payment',
            'Card' => 'Card Payment',
            'Mobile Money' => 'Mobile Money',
            'Flutterwave' => 'Flutterwave',
        ];
    }

    public static function getPaymentStatuses()
    {
        return [
            'Success' => 'Successful',
            'Pending' => 'Pending',
            'Failed' => 'Failed',
        ];
    }

    public static function getTotalPaymentsByMethod($method, $dateRange = null)
    {
        $query = static::byMethod($method)->successful();
        
        if ($dateRange) {
            $query->dateRange($dateRange['start'], $dateRange['end']);
        }
        
        return $query->sum('amount_paid');
    }

    public static function getPaymentStatistics($dateRange = null)
    {
        $query = static::query();
        
        if ($dateRange) {
            $query->dateRange($dateRange['start'], $dateRange['end']);
        }
        
        return [
            'total_amount' => $query->sum('amount_paid'),
            'total_payments' => $query->count(),
            'successful_payments' => $query->clone()->successful()->count(),
            'pending_payments' => $query->clone()->pending()->count(),
            'failed_payments' => $query->clone()->failed()->count(),
            'average_amount' => $query->avg('amount_paid'),
            'highest_amount' => $query->max('amount_paid'),
        ];
    }

    public static function searchPayments($term)
    {
        return static::search($term)
                     ->with(['consultation', 'consultation.patient', 'cashReceiver'])
                     ->orderBy('payment_date', 'desc')
                     ->get();
    }

    public static function prepare($m)
    {
        $consultation = Consultation::find($m->consultation_id);
        if ($consultation == null) {
            throw new \Exception('Consultation not found.');
        }
        $m->description = 'Paid ' . number_format($m->amount_paid) . ' for consultation ' . $consultation->consultation_number . ", " . $consultation->services_text . ".";
        $m->amount_payable = $consultation->total_due;
        $m->balance = $m->amount_payable - $m->amount_paid;

        //if m->balance is less than 0, then throw exception

        if ($m->payment_date == null || strlen($m->payment_date) < 5) {
            $m->payment_date = date('Y-m-d H:i:s');
        }
        //payment_time
        if ($m->payment_time == null || strlen($m->payment_time) < 5) {
            $m->payment_time = date('Y-m-d H:i:s');
        }

        if ($m->payment_method == 'Cash') {
            $receiver = User::find($m->cash_received_by_id);
            if ($receiver == null) {
                throw new \Exception('Cash receiver not found.');
            }
            $m->description = number_format($m->amount_paid) . ' cash received by ' . $receiver->name . ' for consultation ' . $consultation->consultation_number . ", " . $consultation->services_text . ".";
            $m->payment_reference = $m->cash_receipt_number;
            $m->payment_status = 'Success';
        } else if ($m->payment_method == 'Mobile Money') {
            $m->payment_phone_number = Utils::prepare_phone_number($m->payment_phone_number);
            if (!Utils::phone_number_is_valid($m->payment_phone_number)) {
                throw new \Exception('Invalid phone number.');
            }
            $m->payment_status = 'Success';
        } else if ($m->payment_method == 'Flutterwave') {
            $m->payment_status = 'Success';
            //generate flutterwave_payment_link
        }
        if ($m->payment_method == 'Card') {
            $card = User::find($m->card_id);
            if ($card == null) {
                throw new \Exception('Card not found.');
            }
            if ($card->is_dependent == 'Yes') {
                throw new \Exception('Dependent card cannot be used');
            }

            //card_status
            if ($card->card_status != 'Active') {
                throw new \Exception('Card is not active');
            }

            //card_expiry
            if ($card->card_expiry != null) {
                $card_expiry = Carbon::parse($card->card_expiry);
                if ($card_expiry->lt(Carbon::now())) {
                    throw new \Exception('Card has expired on ' . $card_expiry->format('Y-m-d'));
                }
            }

            $m->payment_status = 'Success';
        } else {
            //throw new \Exception('Invalid payment method.');
        }
        return $m;
    }
}
