<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    protected $table = 'transaction_types';

    protected $fillable = [
        'type_code',
        'type_name',
        'description',
        'category',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Relasi ke model account_transactions
     */
    public function accountTransactions()
    {
        return $this->hasMany(AccountTransactions::class);
    }
}
