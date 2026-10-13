<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\ModelTree;

class Branch extends Model
{
    use ModelTree;

    protected $fillable = [
        'parent_id',
        'order',
        'branch_code',
        'branch_name',
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
}
