<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherEmploymentEvent extends Model
{
    protected $guarded = [];

    protected $casts = ['event_date' => 'date'];

    public const TYPES = [
        'hired'              => 'Hired',
        'promoted'           => 'Promoted',
        'title_changed'      => 'Title Changed',
        'contract_renewed'   => 'Contract Renewed',
        'department_change'  => 'Department Change',
        'left'               => 'Left',
        'returned'           => 'Returned',
        'note'               => 'Note',
    ];

    public const TYPE_COLORS = [
        'hired' => 'success', 'promoted' => 'success', 'title_changed' => 'info',
        'contract_renewed' => 'info', 'department_change' => 'warning',
        'left' => 'danger', 'returned' => 'success', 'note' => 'gray',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
