<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ItemsContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'item_id',
    ];

    public function loans() : BelongsTo
    {
        return $this->belongsTo(Loan::class,'loan_id');
    }

    public function item() : BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
