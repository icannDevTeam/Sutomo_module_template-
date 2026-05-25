<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_employment_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->date('event_date');
            $t->string('event_type'); // hired|promoted|title_changed|contract_renewed|department_change|left|returned|note
            $t->string('from_value')->nullable();
            $t->string('to_value')->nullable();
            $t->text('note')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['teacher_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_employment_events');
    }
};
