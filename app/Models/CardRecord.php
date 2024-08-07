<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardRecord extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $type = strtolower($model->type);

            if ($type != 'credit' && $type != 'debit') {
                throw new \Exception('Invalid type');
            }


            $amount = abs($model->amount);
            if ($type == 'credit') {
                $model->amount = $amount;
            } else {
                $model->amount = (-1) * $amount;
            }

            $card = User::find($model->card_id);
            if ($card == null) {
                throw new \Exception('Card not found');
            }

            $balance = 0;
            if ($card->card_balance != null) {
                $balance = $card->card_balance + $model->amount;
                $model->balance = $balance;
            } else {
                $model->balance = $model->amount;
            }

            $model->company_id = $card->company_id;
            if ($model->payment_date == null || trim($model->payment_date) == '') {
                $model->payment_date = date('Y-m-d H:i:s');
            }

            $model->description = $amount . ' ' . $type . ' on ' . $model->payment_date . ' by ' . $card->card_number . '.';

            //check if remarks is same as payment_remarks and sate to be same as description
            if ($model->payment_remarks == null || trim($model->payment_remarks) == '') {
                $model->payment_remarks = $model->description;
            }
        });


        //created 
        static::created(function ($model) {
            $card = User::find($model->card_id);
            $card->card_balance = CardRecord::where('card_id', $model->card_id)->sum('amount');
            $card->save();
        });

        //updated
        static::updated(function ($model) {
            $card = User::find($model->card_id);
            $card->card_balance = CardRecord::where('card_id', $model->card_id)->sum('amount');
            $card->save();
        });
    }


    //belongs to card_id
    public function card()
    {
        return $this->belongsTo(User::class, 'card_id');
    }

    //belongs to company_id
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
