<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'bank_code',
        'bank_name',
        'account_number',
        'account_holder',
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
}
