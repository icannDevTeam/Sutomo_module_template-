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
        'subjects'       => 'array',
        'past_schools'   => 'array',
        'certifications' => 'array',
        'languages'      => 'array',
        'meta'           => 'array',
        'talent_pool'    => 'boolean',
        'shortlisted'    => 'boolean',
        'applied_at'     => 'date',
    ];

    public const SOURCES = [
        'website'  => 'Website',
        'walk-in'  => 'Walk-in',
        'referral' => 'Referral',
        'agency'   => 'Agency',
        'fair'     => 'Career Fair',
    ];

    public const QUALIFICATIONS = [
        'D3' => 'D3 / Diploma',
        'S1' => 'S1 / Bachelor',
        'S2' => 'S2 / Master',
        'S3' => 'S3 / Doctorate',
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
