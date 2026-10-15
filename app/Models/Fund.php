<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\ModelTree;
use Illuminate\Database\Eloquent\Builder; 

class Fund extends Model
{
    use ModelTree;

    protected $fillable = [
        'parent_id',
        'order',
        'fund_code',
        'fund_name',
        'is_active',
        'description',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];


    public function parent()
    {
        return $this->belongsTo(Fund::class, 'parent_id');
    }

      // Scope to get only root branches (parent_id = -1)
    public function scopeRootFunds(Builder $query)
    {
        return $query->where('is_active', true);
    }
}
