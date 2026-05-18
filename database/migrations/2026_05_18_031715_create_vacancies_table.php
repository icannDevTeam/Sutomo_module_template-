<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('title');
            $t->string('dept');
            $t->string('campus');
            $t->string('type')->default('Full-time');
            $t->string('level')->nullable();
            $t->unsignedInteger('openings')->default(1);
            $t->unsignedInteger('applicants')->default(0);
            $t->date('posted_at');
            $t->date('closes_at');
            $t->string('status')->default('open');
            $t->boolean('featured')->default(false);
            $t->text('summary')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('vacancies'); }
};
