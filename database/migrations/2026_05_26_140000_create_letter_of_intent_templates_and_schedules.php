<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_of_intent_templates', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('audience')->default('all'); // all | by_dept | by_subject | by_position
            $t->string('audience_value')->nullable(); // e.g. dept name, subject name
            $t->text('body');
            $t->string('subject_line')->nullable();
            $t->unsignedInteger('default_deadline_days')->default(14);
            $t->boolean('is_default')->default(false);
            $t->boolean('active')->default(true);
            $t->json('placeholders_used')->nullable();
            $t->timestamps();
        });

        Schema::create('letter_of_intent_schedules', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->foreignId('template_id')->constrained('letter_of_intent_templates')->cascadeOnDelete();
            $t->string('frequency')->default('yearly'); // yearly | monthly | once
            $t->unsignedTinyInteger('day_of_month')->nullable();
            $t->unsignedTinyInteger('month_of_year')->nullable();
            $t->date('run_on')->nullable(); // for once
            $t->string('target_scope')->default('all_active'); // all_active | by_dept | by_subject | by_unit | specific
            $t->json('target_value')->nullable(); // e.g. ['Mathematics','Science'] or [teacher_id,...]
            $t->unsignedInteger('deadline_days')->default(14);
            $t->tinyInteger('academic_year_offset')->default(1); // 0=current AY, +1=next
            $t->boolean('active')->default(true);
            $t->timestamp('last_run_at')->nullable();
            $t->timestamp('next_run_at')->nullable();
            $t->unsignedInteger('last_run_count')->default(0);
            $t->json('last_run_summary')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_of_intent_schedules');
        Schema::dropIfExists('letter_of_intent_templates');
    }
};
