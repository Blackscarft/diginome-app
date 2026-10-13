<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FintechAccount extends Model
{
    protected $fillable = [
        'account_id',
        'fee_account_id',
        'fintech_account_code',
        'fintech_account_name',
        'merchant_id',
        'initial_balance',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'initial_balance' => 'decimal:2'
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function feeAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'fee_account_id');
    }
}
