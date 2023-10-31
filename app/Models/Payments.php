<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'user_id',
        'type_payment',
        'amount',
        'discount',
        'discount_total'
    ];

    //get atrribute type_payment
    /* public function getTypePaymentAttribute($value)
    {
        if($value == 'amortization') {
            $value = 'Amortizacion';
        }elseif($value == 'renovation'){
            $value = 'Renovacion';
        }
        return ucfirst($value);
    } */


    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
