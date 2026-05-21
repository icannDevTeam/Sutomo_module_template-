<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherLoad extends Model
{
    protected $guarded = [];

    protected $casts = [
        'subjects'          => 'array',
        'unavailable_slots' => 'array',
    ];
}
