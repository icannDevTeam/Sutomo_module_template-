<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_substitutes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->foreignId('substitute_teacher_id')->constrained('teachers')->cascadeOnDelete();
            $t->unsignedTinyInteger('rank')->default(1);
            $t->string('note')->nullable();
            $t->timestamps();
            $t->unique(['teacher_id', 'substitute_teacher_id'], 'teacher_sub_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_substitutes');
    }
};
