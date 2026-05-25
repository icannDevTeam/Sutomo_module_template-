<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_certifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('issuer')->nullable();
            $t->string('country', 2)->default('ID');
            $t->boolean('is_government_approved')->default(false);
            $t->string('accreditation_no')->nullable();
            $t->date('issued_at')->nullable();
            $t->date('expires_at')->nullable();
            $t->string('file_path')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['teacher_id', 'is_government_approved']);
            $t->index(['teacher_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_certifications');
    }
};
