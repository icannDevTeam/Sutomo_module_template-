<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('substitute_offers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_leave_id')->constrained()->cascadeOnDelete();
            $t->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete(); // candidate substitute
            $t->string('token', 64)->unique();
            $t->string('status')->default('pending'); // pending|accepted|declined|expired|cancelled
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('responded_at')->nullable();
            $t->text('response_note')->nullable();
            $t->unsignedTinyInteger('round')->default(1); // broadcast round (1 = first 3, 2 = next 3, ...)
            $t->timestamps();

            $t->index(['teacher_leave_id', 'status']);
            $t->unique(['teacher_leave_id', 'teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('substitute_offers');
    }
};
