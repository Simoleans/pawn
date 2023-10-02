<?php

namespace App\Models;

use App\Enums\Condition;
use App\States\ItemState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Item extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'client_id',
        'branch_id',
        'state',
        'condition',
        'estimated_value',
        'currency',
        'image_url',
    ];

    protected $appends = [
        'state_label',
        'state_description',
        'condition_label',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function loans(): BelongsToMany
    {
        return $this->BelongsToMany(Loan::class,'id','loan_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getStateLabelAttribute(): string
    {
        return ItemState::getLabel($this->state);
    }

    public function getStateDescriptionAttribute(): string
    {
        return ItemState::getDescription($this->state);
    }

    public function getConditionLabelAttribute()
    {
        return constant("App\Enums\Condition::$this->condition")->value;
    }
}
