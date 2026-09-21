<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\ModelTree;
use Illuminate\Database\Eloquent\Builder; 

class Account extends Model
{
    use ModelTree;

    protected $table = "accounts";

    protected $fillable = [
        'parent_id',
        'order',
        'code',
        'name',
        'normal_balance',
        'is_postable',
        'is_active',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'order' => 'integer',
        'is_postable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function scopeRootAccounts(Builder $query)
    {
        return $query->where('is_postable', false);
    }
}
