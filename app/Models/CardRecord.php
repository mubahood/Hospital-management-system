<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardRecord extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'card_id',
        'type',
        'amount',
        'description',
        'reference',
        'transaction_date'
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
    }

    /**
     * Handle post-creation logic - called by StandardBootTrait
     */
    protected static function onCreated($model): void
    {
        $card = User::find($model->card_id);
        $card->card_balance = CardRecord::where('card_id', $model->card_id)->sum('amount');
        $card->save();
    }

    /**
     * Handle post-update logic - called by StandardBootTrait
     */
    protected static function onUpdated($model): void
    {
        $card = User::find($model->card_id);
        $card->card_balance = CardRecord::where('card_id', $model->card_id)->sum('amount');
        $card->save();
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
