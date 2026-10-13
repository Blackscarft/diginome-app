<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAccount extends Model
{
    protected $fillable = [
        'account_id',
        'branch_id',
        'cash_account_code',
        'cash_account_name',
        'custodian_name',
        'initial_balance',
        'maximum_balance',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
