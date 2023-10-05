<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataAfterLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'payment_id',
        'capital',
        'interest_rate',
        'legal_interest',
        'conservation_expense',
        'utility',
        'balance_pay',
    ];
}
