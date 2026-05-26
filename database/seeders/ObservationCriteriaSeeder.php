<?php

namespace Database\Seeders;

use App\Models\ObservationCriterion;
use Illuminate\Database\Seeder;

class ObservationCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['key' => 'engagement',       'label' => 'Engagement',           'description' => 'Captures and sustains student attention; active participation across the class.'],
            ['key' => 'clarity',          'label' => 'Clarity of Instruction','description' => 'Explanations are clear, well-sequenced, and pitched to learners.'],
            ['key' => 'pacing',           'label' => 'Pacing',                'description' => 'Lesson moves at an appropriate tempo; transitions are smooth.'],
            ['key' => 'classroom_mgmt',   'label' => 'Classroom Management',  'description' => 'Routines, behaviour expectations and time-on-task are maintained.'],
            ['key' => 'subject_mastery',  'label' => 'Subject Mastery',       'description' => 'Demonstrates accurate, deep content knowledge.'],
            ['key' => 'differentiation',  'label' => 'Differentiation',       'description' => 'Adapts tasks and supports for varied learner needs.'],
            ['key' => 'assessment',       'label' => 'Assessment for Learning','description' => 'Checks for understanding and adjusts instruction in response.'],
            ['key' => 'student_rapport',  'label' => 'Student Rapport',       'description' => 'Builds positive relationships; respectful, encouraging tone.'],
        ];

        foreach ($rows as $i => $r) {
            ObservationCriterion::updateOrCreate(
                ['key' => $r['key']],
                [
                    'label'       => $r['label'],
                    'description' => $r['description'],
                    'weight'      => 5,
                    'order'       => $i + 1,
                    'active'      => true,
                ],
            );
        }
    }
}
