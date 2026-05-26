<?php

namespace Database\Seeders;

use App\Models\LetterOfIntentSchedule;
use App\Models\LetterOfIntentTemplate;
use Illuminate\Database\Seeder;

/**
 * Seeds default LOI templates + a sample annual schedule.
 * Idempotent — uses updateOrCreate keyed by slug/name.
 */
class LetterOfIntentConfigSeeder extends Seeder
{
    public function run(): void
    {
        $standardBody = <<<'TXT'
Dear {{teacher_name}},

Following your continued contribution as {{position}} during the {{academic_year_prev}} academic
year, the School Leadership is pleased to formally invite you to re-commit to Sutomo School
for the {{academic_year}} academic year.

By signing this Letter of Intent you confirm:
  1. Your intention to continue serving as {{position}} for the {{academic_year}} academic year.
  2. Your acknowledgement that contract terms (salary, allowances, leave) will be issued
     separately, no later than two weeks after this letter is signed.
  3. Your acceptance of the published academic calendar and duty schedule.

This letter is not a binding contract by itself — it is a non-binding indication of mutual intent
that allows the school to finalise staffing and timetabling.

Kindly review and sign or decline before {{deadline}}.

Warm regards,
{{principal_name}}
Principal — {{school_name}}
TXT;

        $homeroomBody = <<<'TXT'
Dear {{teacher_name}},

In recognition of your role as a Homeroom Teacher during {{academic_year_prev}}, we are pleased
to invite you to continue serving in this capacity for {{academic_year}}.

Homeroom Teachers play a vital pastoral role at {{school_name}}, including:
  • Daily class supervision and attendance
  • Parent liaison and quarterly progress conferences
  • Coordination of student wellbeing and behaviour matters

Please confirm your intent by {{deadline}}. The formal contract addendum will follow within two
weeks of signing.

Warm regards,
{{principal_name}}
Principal — {{school_name}}
TXT;

        $standard = LetterOfIntentTemplate::updateOrCreate(
            ['slug' => 'annual-recommitment-standard'],
            [
                'name'                  => 'Annual Re-commitment — Standard',
                'audience'              => 'all',
                'audience_value'        => null,
                'subject_line'          => 'Letter of Intent — {{academic_year}}',
                'default_deadline_days' => 14,
                'is_default'            => true,
                'active'                => true,
                'body'                  => $standardBody,
            ],
        );

        LetterOfIntentTemplate::updateOrCreate(
            ['slug' => 'annual-recommitment-homeroom'],
            [
                'name'                  => 'Annual Re-commitment — Homeroom',
                'audience'              => 'by_position',
                'audience_value'        => 'Homeroom',
                'subject_line'          => 'Letter of Intent (Homeroom) — {{academic_year}}',
                'default_deadline_days' => 14,
                'is_default'            => false,
                'active'                => true,
                'body'                  => $homeroomBody,
            ],
        );

        $schedule = LetterOfIntentSchedule::updateOrCreate(
            ['name' => 'Annual LOI — April batch'],
            [
                'template_id'          => $standard->id,
                'frequency'            => 'yearly',
                'month_of_year'        => 4,
                'day_of_month'         => 15,
                'run_on'               => null,
                'target_scope'         => 'all_active',
                'target_value'         => [],
                'deadline_days'        => 14,
                'academic_year_offset' => 1,
                'active'               => true,
            ],
        );
        $schedule->next_run_at = $schedule->computeNextRun();
        $schedule->save();
    }
}
