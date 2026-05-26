<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterOfIntentTemplate extends Model
{
    protected $table = 'letter_of_intent_templates';

    protected $fillable = [
        'name', 'slug', 'audience', 'audience_value', 'body', 'subject_line',
        'default_deadline_days', 'is_default', 'active', 'placeholders_used',
    ];

    protected $casts = [
        'is_default'         => 'bool',
        'active'             => 'bool',
        'placeholders_used'  => 'array',
    ];

    public const AUDIENCES = [
        'all'         => 'All teachers',
        'by_dept'     => 'By department',
        'by_subject'  => 'By subject',
        'by_position' => 'By position',
    ];

    public const PLACEHOLDERS = [
        '{{teacher_name}}', '{{position}}', '{{department}}', '{{subject}}',
        '{{academic_year}}', '{{academic_year_prev}}', '{{deadline}}',
        '{{principal_name}}', '{{school_name}}', '{{today}}',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(LetterOfIntentSchedule::class, 'template_id');
    }
}
