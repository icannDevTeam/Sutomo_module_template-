<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('gender', 1)->nullable();
            $t->foreignId('vacancy_id')->nullable()->constrained()->nullOnDelete();
            $t->string('stage')->default('applied');
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('city')->nullable();
            $t->unsignedSmallInteger('age')->nullable();
            $t->string('education')->nullable();
            $t->unsignedSmallInteger('years')->default(0);
            $t->json('subjects')->nullable();
            $t->date('applied_at')->nullable();
            $t->string('priority')->default('normal');
            $t->unsignedTinyInteger('score_written')->nullable();
            $t->unsignedTinyInteger('score_interview')->nullable();
            $t->unsignedTinyInteger('score_micro')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('candidates'); }
};
