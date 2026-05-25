<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_clearances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->string('type'); // criminal_record|child_protection|medical|vaccination|drug_test|other
            $t->string('issuer')->nullable();
            $t->date('issued_at')->nullable();
            $t->date('expires_at')->nullable();
            $t->string('file_path')->nullable();
            $t->string('status')->default('valid'); // valid|expiring|expired|pending
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['teacher_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_clearances');
    }
};
