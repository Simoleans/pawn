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
        'sale_price',
        'currency',
        'image_url',
        'loan_id',
    ];

    protected $appends = [
        'state_label',
        'state_description',
        'condition_label',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::created(function ($item) {
            $item->client_id = $item->loan->client_id;
            $item->branch_id = $item->loan->branch_id;
            $item->save();
        });

    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function loan(): BelongsTo
    {
        return $this->BelongsTo(Loan::class);
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
