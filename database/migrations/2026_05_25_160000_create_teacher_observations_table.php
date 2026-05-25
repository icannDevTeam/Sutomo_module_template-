<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_observations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->foreignId('observer_id')->nullable()->constrained('users')->nullOnDelete();
            $t->dateTime('observed_at');
            $t->string('lesson_subject')->nullable();
            $t->string('lesson_class_code')->nullable();
            $t->json('dimensions')->nullable(); // {engagement,clarity,pacing,classroom_mgmt}
            $t->text('strengths')->nullable();
            $t->text('action_items')->nullable();
            $t->date('follow_up_date')->nullable();
            $t->unsignedBigInteger('supervisi_evaluation_id')->nullable();
            $t->timestamps();
            $t->index(['teacher_id', 'observed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_observations');
    }
};
