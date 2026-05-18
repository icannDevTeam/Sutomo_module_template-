<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $t->date('scheduled_date');
            $t->string('scheduled_time')->nullable();
            $t->string('room')->nullable();
            $t->json('panel')->nullable();
            $t->string('type')->default('Panel Interview');
            $t->string('status')->default('scheduled');
            $t->string('recommendation')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('interviews'); }
};
