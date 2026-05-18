<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('amount');
            $t->string('status')->default('pending');
            $t->date('paid_at')->nullable();
            $t->date('due_date')->nullable();
            $t->boolean('refund_eligible')->default(false);
            $t->string('receipt')->nullable();
            $t->string('bank')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('deposits'); }
};
