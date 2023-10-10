<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'code',
        'user_id',
        'document',
        'branch_id',
        'address',
        'phone',
        'email',
        'mobile',
        'issued',
    ];
    protected $guarded = [
        'id',
        'code',
    ];

    protected $appends = [
        'full_name'
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($client) {
            $client->user_id = auth()->id() ?? User::query()->first()->id;
            //branch_id
            //$client->branch_id = auth()->user()->branch_id;
        });

        static::created(function ($client) {
            $client->code = self::generateCode($client->first_name, $client->last_name, $client->id);
            $client->save();
        });

        static::updating(function ($client) {
            if ($client->isDirty('first_name') || $client->isDirty('last_name')) {
                $client->code = self::generateCode($client->first_name, $client->last_name, $client->id);
            }
        });
    }

    /**
     * Generates a code based on the customer's first name, last name and client ID
     *
     * @param string $firstName
     * @param string $lastName
     * @param int $id
     * @return string
     */
    public static function generateCode(string $firstName, string $lastName, int $id): string
    {
        $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
        $idWithLeadingZeros = str_pad($id, 4, '0', STR_PAD_LEFT);

        return "{$initials}-{$idWithLeadingZeros}";
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function personalReferences(): HasMany
    {
        return $this->hasMany(PersonalReference::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public static function getFullNameQuery()
    {
        return self::select(
            \DB::raw('CONCAT(first_name, " ", last_name) as name'),
            'id'
        );
    }
}
