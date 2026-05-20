<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('placement_exam_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('enrollment_period_id')->constrained()->cascadeOnDelete();
            $t->string('subject');                       // e.g. Matematika, Bahasa Indonesia, Wawancara
            $t->string('room')->nullable();
            $t->dateTime('starts_at');
            $t->unsignedSmallInteger('duration_minutes')->default(90);
            $t->foreignId('supervisor_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $t->string('grade_band')->nullable();       // SD / SMP / SMA / All
            $t->unsignedSmallInteger('capacity')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['enrollment_period_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_exam_sessions');
    }
};
