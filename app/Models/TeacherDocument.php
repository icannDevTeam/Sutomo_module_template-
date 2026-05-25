<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherDocument extends Model
{
    protected $guarded = [];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'expires_at'  => 'date',
    ];

    public const TYPES = [
        'ktp'      => 'KTP',
        'npwp'     => 'NPWP',
        'ijazah'   => 'Ijazah / Diploma',
        'passport' => 'Passport',
        'kk'       => 'Kartu Keluarga',
        'contract' => 'Contract',
        'other'    => 'Other',
    ];

    public const STATUSES = [
        'pending'  => 'Pending',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
    ];

    public const STATUS_COLORS = [
        'pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
