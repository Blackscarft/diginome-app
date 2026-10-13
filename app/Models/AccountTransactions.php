<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransactions extends Model
{
    use SoftDeletes;
    
    protected $table = 'account_transactions';

    protected $fillable  = [
        'accountable_type',
        'accountable_id',
        'transaction_type_id',
        'debit',
        'credit',
        'transaction_date',
        'transaction_time',
        'description',
        'reference',
        'fee_amount',
        'is_matched',
    ];

    /**
     * Relasi polimorfik ke model akun (Bank, Cash, Fintech, dll).
     */
    public function accountable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relasi ke model TransactionType
     */
    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'transaction_types');
    }

}
