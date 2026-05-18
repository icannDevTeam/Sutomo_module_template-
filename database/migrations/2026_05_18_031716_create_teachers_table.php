<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('employee_no')->nullable();
            $t->string('name');
            $t->string('gender', 1)->nullable();
            $t->date('dob')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('subject')->nullable();
            $t->string('dept')->nullable();
            $t->string('campus')->nullable();
            $t->string('status')->default('permanent');
            $t->string('employment')->default('full-time');
            $t->date('joined_at')->nullable();
            $t->string('tenure')->nullable();
            $t->string('contract')->nullable();
            $t->date('contract_end')->nullable();
            $t->string('education')->nullable();
            $t->json('certifications')->nullable();
            $t->json('languages')->nullable();
            $t->string('city')->nullable();
            $t->decimal('rating', 3, 1)->nullable();
            $t->date('last_review')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('teachers'); }
};
