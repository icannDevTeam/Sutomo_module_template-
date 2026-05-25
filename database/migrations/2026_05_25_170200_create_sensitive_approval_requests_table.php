<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sensitive_approval_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('target_teacher_id')->constrained('teachers')->cascadeOnDelete();
            $t->string('action_type'); // salary_change|contract_terminate|title_demotion
            $t->json('payload');
            $t->text('reason')->nullable();
            $t->foreignId('first_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('second_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('first_approved_at')->nullable();
            $t->timestamp('second_approved_at')->nullable();
            $t->string('status')->default('pending_first'); // pending_first|pending_second|approved|rejected
            $t->text('rejection_reason')->nullable();
            $t->timestamps();
            $t->index(['target_teacher_id', 'status']);
            $t->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensitive_approval_requests');
    }
};
