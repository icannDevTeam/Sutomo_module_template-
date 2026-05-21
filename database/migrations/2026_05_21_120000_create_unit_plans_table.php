<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('unit_plans', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('grade');                  // e.g. "Grade 2"
            $t->string('campus')->nullable();     // sd|smp|sma
            $t->string('theme')->nullable();      // PYP-style transdisciplinary theme
            $t->string('central_idea', 500)->nullable();
            $t->json('subjects')->nullable();     // ['English','Mathematics',...]
            $t->json('sections')->nullable();     // keyed sections: lines_of_inquiry, learner_profile, lessons, etc.
            $t->string('cover_emoji', 8)->nullable();
            $t->string('cover_color', 32)->nullable(); // tailwind-like token
            $t->string('status')->default('draft'); // draft|active|completed|archived
            $t->unsignedTinyInteger('completion_pct')->default(0);
            $t->date('starts_on')->nullable();
            $t->date('ends_on')->nullable();
            $t->string('owner_name')->nullable();
            $t->string('owner_role')->nullable();
            $t->dateTime('last_activity_at')->nullable();
            $t->timestamps();
            $t->index(['status', 'last_activity_at']);
            $t->index('grade');
        });

        Schema::create('unit_plan_collaborators', function (Blueprint $t) {
            $t->id();
            $t->foreignId('unit_plan_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('role')->default('editor'); // owner|editor|viewer|reviewer
            $t->string('initials', 4)->nullable();
            $t->string('color', 16)->nullable();   // hex
            $t->dateTime('last_seen_at')->nullable();
            $t->boolean('online_now')->default(false);
            $t->timestamps();
            $t->index('unit_plan_id');
        });

        Schema::create('unit_plan_comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('unit_plan_id')->constrained()->cascadeOnDelete();
            $t->string('section_key')->nullable();   // null = whole-unit
            $t->string('author_name');
            $t->string('author_initials', 4)->nullable();
            $t->string('author_color', 16)->nullable();
            $t->text('body');
            $t->boolean('resolved')->default(false);
            $t->timestamps();
            $t->index(['unit_plan_id', 'section_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_plan_comments');
        Schema::dropIfExists('unit_plan_collaborators');
        Schema::dropIfExists('unit_plans');
    }
};
