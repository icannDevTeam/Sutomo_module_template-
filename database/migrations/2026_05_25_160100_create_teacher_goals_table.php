<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_goals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->date('target_date')->nullable();
            $t->unsignedTinyInteger('progress')->default(0);
            $t->string('status')->default('on_track'); // on_track|at_risk|done|cancelled
            $t->string('academic_year')->nullable();
            $t->date('set_during_review_at')->nullable();
            $t->timestamps();
            $t->index(['teacher_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_goals');
    }
};
