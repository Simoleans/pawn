<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Loan extends Model
{
    use HasFactory;

    protected $guard = [
        'id',
    ];
    protected $fillable = [
        //'code',
        'client_id',
        'branch_id',
        'state',
        'currency',
        'capital',
        'interest_rate',
        'legal_interest',
        'conservation_expense',
        'utility',
        'balance_pay',
        'date_contract',
        'date_contract_expiration',
        'user_id',
        'renovation',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($loan) {
            $loan->user_id = auth()->id() ?? User::query()->first()->id;
        });

        static::created(function ($loan) {
            $loan->code = self::generateCode($loan->id);
            $loan->code_contract = self::generateCodeContract($loan->client->first_name,$loan->client->last_name,$loan->id,$loan->client->code);
            $loan->save();
        });

    }

    public function client() : BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items() : HasMany
    {
        return $this->hasMany(ItemsContract::class,'loan_id');
    }

    public function contract_articles() : HasMany {
        return $this->hasMany(ContractArticle::class,'loan_id');
    }


    public static function generateCode(int $id): string
    {
        $initials = 'PRES';
        $idWithLeadingZeros = str_pad($id, 4, '0', STR_PAD_LEFT);

        return "{$initials}-{$idWithLeadingZeros}";
    }

    public static function generateCodeContract(string $firstName, string $lastName, int $id,string $codeClient): string
    {
        $code = explode('-',$codeClient);
        $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
        $clientCodeNumber = str_pad($code[1], 4, '0', STR_PAD_LEFT);
        $idWithLeadingZeros = str_pad($id, 4, '0', STR_PAD_LEFT);

        return "{$initials}-{$clientCodeNumber}-{$idWithLeadingZeros}";
    }

}
