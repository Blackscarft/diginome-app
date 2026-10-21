<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransaction extends Model
{
    use SoftDeletes;
    
    protected $table = 'account_transactions';

    protected $fillable  = [
        'accountable_type',
        'accountable_id',
        'transaction_types',
        'debit',
        'credit',
        'transaction_date',
        'transaction_time',
        'description',
        'reference',
        'fee_amount',
        'is_matched',
    ];

    protected $casts = [
        'is_matched' => 'boolean',
        'transaction_date' => 'date',
        'transaction_types' => 'integer',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'fee_amount' => 'decimal:2'
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
