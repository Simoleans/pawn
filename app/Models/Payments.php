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


    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
