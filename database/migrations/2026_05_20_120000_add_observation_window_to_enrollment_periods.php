<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Observation window lives on the EnrollmentPeriod so an entire cohort
 * shares one 5-day attendance schedule. Per-student attendance is still
 * tracked in applications.meta.observation.days.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollment_periods', function (Blueprint $t) {
            $t->date('observation_start_date')->nullable()->after('exam_starts_at');
            $t->unsignedTinyInteger('observation_days')->default(5)->after('observation_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_periods', function (Blueprint $t) {
            $t->dropColumn(['observation_start_date', 'observation_days']);
        });
    }
};
