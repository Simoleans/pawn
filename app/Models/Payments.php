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
    ];

    public function loan()
    {
        return $this->belongsTo(Loans::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
