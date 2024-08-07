<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'payment_channel'
    ];

    //boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $consultation = Consultation::find($model->consultation_id);
            if ($consultation == null) {
                throw new \Exception('Consultation not found.');
            }
            //main_status
            if ($consultation->main_status == 'Completed') {
                throw new \Exception('Consultation already completed.');
            }
            $model = PaymentRecord::prepare($model);
            return $model;
        });

        static::updating(function ($model) {
            $model = PaymentRecord::prepare($model);
            return $model;
        });
        //created
        static::created(function ($model) {
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
        });

        //updated
        static::updated(function ($model) {
            $consultation = Consultation::find($model->consultation_id);
            if ($consultation == null) {
                throw new \Exception('Consultation not found.');
            }
            $consultation->process_balance();
        });

        //deleting
        static::deleting(function ($model) {
            $u = Admin::auth()->user();
            $consultation = Consultation::find($model->consultation_id);
            if ($consultation == null) {
                throw new \Exception('Consultation not found.');
            }
            $consultation->process_balance();
        });
    }

    //eblongs to consultation
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
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
            if (!Utils::is_valid_phone_number($m->payment_phone_number)) {
                throw new \Exception('Invalid phone number.');
            }
            $m->payment_status = 'Success';
        } else if ($m->payment_method == 'Flutterwave') {
            $m->payment_status = 'Pending';
            //generate flutterwave_payment_link
        } else {
            //throw new \Exception('Invalid payment method.');
        }
        return $m;
    }
}
