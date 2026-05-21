<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timetable_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('definition_id')->constrained('timetable_definitions')->cascadeOnDelete();
            $table->string('label');                    // e.g. "Published 2026-05-21"
            $table->string('published_by')->nullable(); // user name
            $table->timestamp('published_at');
            $table->unsignedInteger('lesson_count')->default(0);
            $table->unsignedInteger('conflict_count')->default(0);
            $table->json('payload');                    // full frozen lessons array
            $table->timestamps();
            $table->index(['definition_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_snapshots');
    }
};
