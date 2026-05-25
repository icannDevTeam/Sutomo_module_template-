<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_journal_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->date('entry_date');
            $t->text('body');
            $t->boolean('is_private')->default(true);
            $t->boolean('shared_with_mentor')->default(false);
            $t->timestamps();
            $t->index(['teacher_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_journal_entries');
    }
};
