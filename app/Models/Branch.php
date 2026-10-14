<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\ModelTree;
use Illuminate\Database\Eloquent\Builder; 

class Branch extends Model
{
    use ModelTree;

    protected $fillable = [
        'parent_id',
        'order',
        'branch_code',
        'branch_name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Branch::class, 'parent_id');
    }

    // Scope to get only root branches (parent_id = -1)
    public function scopeRootBranches(Builder $query)
    {
        return $query->where('is_active', true);
    }
}
