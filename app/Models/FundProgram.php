<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder; 

class FundProgram extends Model
{
    protected $table = 'fund_programs';

    protected $fillable = [
        'parent_id',
        'fund_id',
        'fund_program_code',
        'fund_program_name',
        'description',
        'category',
        'initial_balance',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'initial_balance' => 'decimal:2',
    ];

    public function fund(): BelongsTo
    {
        return $this->belongsTo(Fund::class);
    }

    public function parent()
    {
        return $this->belongsTo(FundProgram::class, 'parent_id');
    }

    // Scope to get only root accounts (is_postable = false)
    public function scopeRootAccounts(Builder $query)
    {
        return $query->where('parent_id', -1)
                    ->where('category', 'Kolektif');
    }
}
