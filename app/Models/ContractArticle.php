<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'estimated_value',
        'currency',
        'image_url',
        'condition',
        'loan_id',
        'category_id',
    ];
}
