<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parent_communications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $t->string('parent_name')->nullable();
            $t->string('contact_method'); // call|whatsapp|email|in_person
            $t->dateTime('occurred_at');
            $t->text('summary')->nullable();
            $t->timestamps();
            $t->index(['teacher_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_communications');
    }
};
