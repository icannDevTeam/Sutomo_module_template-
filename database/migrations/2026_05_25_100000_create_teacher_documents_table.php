<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->string('type'); // ktp / npwp / ijazah / passport / other
            $t->string('label')->nullable();
            $t->string('file_path')->nullable();
            $t->string('original_name')->nullable();
            $t->timestamp('uploaded_at')->nullable();
            $t->date('expires_at')->nullable();
            $t->string('status')->default('pending'); // pending / verified / rejected
            $t->string('verified_by')->nullable();
            $t->timestamp('verified_at')->nullable();
            $t->text('note')->nullable();
            $t->timestamps();
            $t->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_documents');
    }
};
