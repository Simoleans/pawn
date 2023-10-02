<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsMorph extends Model
{
    use HasFactory;

    public $table = 'itemsLoan';

    protected $fillable = [
        'loan_id',
        'itemsLoanable',
        //'itemsLoanable_type',
    ];

    public function items()
    {
        return $this->morphToMany(Item::class,'itemsLoanable');
    }


}
