<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_compensation', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->text('base_salary');           // encrypted
            $t->text('allowances')->nullable();// encrypted json
            $t->string('currency', 3)->default('IDR');
            $t->date('effective_from');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['teacher_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_compensation');
    }
};
