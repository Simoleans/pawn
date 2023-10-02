<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'full_name',
        'mobile',
        'relationship',
        'address',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
