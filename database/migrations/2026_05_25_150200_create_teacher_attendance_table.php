<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_attendance', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->string('status'); // present|absent|late|leave|holiday
            $t->dateTime('check_in_at')->nullable();
            $t->dateTime('check_out_at')->nullable();
            $t->string('source')->default('manual'); // manual|device|system
            $t->text('note')->nullable();
            $t->timestamps();
            $t->unique(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance');
    }
};
