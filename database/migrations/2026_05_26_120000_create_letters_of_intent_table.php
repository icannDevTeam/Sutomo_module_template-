<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('letters_of_intent', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->foreignId('principal_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('academic_year');
            $t->string('position')->nullable();
            $t->text('body');
            $t->string('status')->default('draft'); // draft|sent|signed|declined|expired
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('signed_at')->nullable();
            $t->timestamp('deadline_at')->nullable();
            $t->string('signature_text')->nullable();
            $t->string('signature_ip', 45)->nullable();
            $t->text('decline_reason')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['teacher_id', 'status']);
            $t->index(['status', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letters_of_intent');
    }
};
