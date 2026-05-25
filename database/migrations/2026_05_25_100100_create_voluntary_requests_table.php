<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voluntary_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->string('program');
            $t->text('reason')->nullable();
            $t->timestamp('submitted_at')->nullable();
            $t->string('status')->default('pending'); // pending / approved / declined
            $t->string('decided_by')->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->text('decision_note')->nullable();
            $t->timestamps();
            $t->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voluntary_requests');
    }
};
