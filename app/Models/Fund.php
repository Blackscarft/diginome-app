<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\ModelTree;

class Fund extends Model
{
    use ModelTree;

    protected $fillable = [
        'parent_id',
        'order',
        'fund_code',
        'fund_name',
        'is_active'
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
}
