<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1: enable per-AY/per-semester aggregation for Probation Watch
 * and Contract Renewal pages, and distinguish principal supervisions
 * from peer observations (PKWT-I needs both counted separately).
 *
 * Backfilled for existing rows by the seeder using DutyAssignment::academicYearFor().
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('teacher_observations', function (Blueprint $t) {
            $t->string('academic_year')->nullable()->after('observed_at');
            $t->unsignedTinyInteger('semester')->nullable()->after('academic_year'); // 1 | 2
            // principal_supervision | peer_observation | external | self
            $t->string('observation_type', 32)->default('principal_supervision')->after('semester');

            $t->index(['teacher_id', 'academic_year', 'semester'], 'tobs_teacher_ay_sem_idx');
            $t->index(['observation_type', 'status'], 'tobs_type_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_observations', function (Blueprint $t) {
            $t->dropIndex('tobs_teacher_ay_sem_idx');
            $t->dropIndex('tobs_type_status_idx');
            $t->dropColumn(['academic_year', 'semester', 'observation_type']);
        });
    }
};
