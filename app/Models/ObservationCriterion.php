<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObservationCriterion extends Model
{
    protected $table = 'observation_criteria';

    protected $fillable = ['key', 'label', 'description', 'weight', 'order', 'active'];

    protected $casts = [
        'active' => 'bool',
        'weight' => 'int',
        'order'  => 'int',
    ];
}
