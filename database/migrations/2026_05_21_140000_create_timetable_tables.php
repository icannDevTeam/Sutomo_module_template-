<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timetable_definitions', function (Blueprint $t) {
            $t->id();
            $t->string('term');                 // e.g. "TP 2025/2026"
            $t->string('school_unit');          // e.g. "SMP Swasta Sutomo 1" or "ALL"
            $t->string('status')->default('draft'); // draft | published
            $t->json('meta')->nullable();
            $t->timestamps();
            $t->unique(['term', 'school_unit']);
        });

        Schema::create('timetable_lessons', function (Blueprint $t) {
            $t->id();
            $t->foreignId('definition_id')->constrained('timetable_definitions')->cascadeOnDelete();
            $t->string('teacher_ref', 64);          // string id (T-001 etc) until full FK
            $t->string('class_code', 32);
            $t->string('subject', 64);
            $t->string('day', 16);                  // SENIN..SABTU
            $t->string('period', 8);                // I, II, III ...
            $t->string('session', 8);               // pagi | sore
            $t->string('room', 32)->nullable();
            $t->boolean('locked')->default(false);  // for Phase 5 auto-generator
            $t->json('conflicts')->nullable();      // cached flags from ConflictService
            $t->timestamps();

            $t->index(['definition_id', 'teacher_ref', 'day', 'period', 'session'], 'tt_teacher_slot');
            $t->index(['definition_id', 'class_code', 'day', 'period', 'session'], 'tt_class_slot');
            $t->index(['definition_id', 'room', 'day', 'period', 'session'], 'tt_room_slot');
        });

        Schema::create('subject_requirements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('definition_id')->nullable()->constrained('timetable_definitions')->cascadeOnDelete();
            $t->string('grade_level', 16);          // "7", "8", "4" etc
            $t->string('subject', 64);
            $t->unsignedSmallInteger('weekly_hours')->default(0);
            $t->timestamps();
            $t->unique(['definition_id', 'grade_level', 'subject'], 'sr_def_grade_subj');
        });

        Schema::create('teacher_loads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('definition_id')->nullable()->constrained('timetable_definitions')->cascadeOnDelete();
            $t->string('teacher_ref', 64);
            $t->unsignedSmallInteger('weekly_cap')->default(24);
            $t->json('subjects')->nullable();           // [] of subject names this teacher may teach
            $t->json('unavailable_slots')->nullable(); // [{day, period, session}]
            $t->timestamps();
            $t->unique(['definition_id', 'teacher_ref'], 'tl_def_teacher');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_loads');
        Schema::dropIfExists('subject_requirements');
        Schema::dropIfExists('timetable_lessons');
        Schema::dropIfExists('timetable_definitions');
    }
};
