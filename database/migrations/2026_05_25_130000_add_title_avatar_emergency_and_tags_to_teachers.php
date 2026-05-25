<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            if (! Schema::hasColumn('teachers', 'title')) {
                $t->string('title', 64)->nullable()->after('name')->index();
            }
            if (! Schema::hasColumn('teachers', 'avatar_path')) {
                $t->string('avatar_path')->nullable()->after('email');
            }
            if (! Schema::hasColumn('teachers', 'emergency_contact_name')) {
                $t->string('emergency_contact_name')->nullable();
            }
            if (! Schema::hasColumn('teachers', 'emergency_contact_relation')) {
                $t->string('emergency_contact_relation', 64)->nullable();
            }
            if (! Schema::hasColumn('teachers', 'emergency_contact_phone')) {
                $t->string('emergency_contact_phone', 32)->nullable();
            }
        });

        Schema::create('teacher_tags', function (Blueprint $t) {
            $t->id();
            $t->string('name', 64)->unique();
            $t->string('color', 16)->nullable();
            $t->timestamps();
        });

        Schema::create('teacher_teacher_tag', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->foreignId('teacher_tag_id')->constrained()->cascadeOnDelete();
            $t->unique(['teacher_id', 'teacher_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_teacher_tag');
        Schema::dropIfExists('teacher_tags');
        Schema::table('teachers', function (Blueprint $t) {
            foreach (['title','avatar_path','emergency_contact_name','emergency_contact_relation','emergency_contact_phone'] as $c) {
                if (Schema::hasColumn('teachers', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
