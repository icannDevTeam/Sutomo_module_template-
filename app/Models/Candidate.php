<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Candidate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'subjects'   => 'array',
        'meta'       => 'array',
        'applied_at' => 'date',
    ];

    public const STAGES = [
        'applied'   => 'Applied',
        'screening' => 'Screening',
        'written'   => 'Written Test',
        'interview' => 'Interview',
        'psycho'    => 'Psycho Test',
        'medical'   => 'Medical',
        'yayasan'   => 'Yayasan Review',
        'opl'       => 'OPL',
        'active'    => 'Active Teacher',
        'rejected'  => 'Rejected',
    ];

    public const STAGE_COLORS = [
        'applied'   => 'gray',
        'screening' => 'info',
        'written'   => 'info',
        'interview' => 'warning',
        'psycho'    => 'primary',
        'medical'   => 'success',
        'yayasan'   => 'danger',
        'opl'       => 'warning',
        'active'    => 'success',
        'rejected'  => 'danger',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function deposit(): HasOne
    {
        return $this->hasOne(Deposit::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }
}
