<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('duty_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('location')->nullable();
            $t->timestamp('starts_at');
            $t->timestamp('ends_at')->nullable();
            $t->string('assigned_by')->nullable();
            $t->string('status')->default('pending'); // pending / accepted / declined / completed
            $t->text('decline_reason')->nullable();
            $t->timestamp('responded_at')->nullable();
            $t->timestamps();
            $t->index(['status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_assignments');
    }
};
