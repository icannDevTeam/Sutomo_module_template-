<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('message_threads', function (Blueprint $t) {
            $t->boolean('is_group')->default(false)->after('category');
            $t->string('group_name')->nullable()->after('is_group');
        });

        Schema::create('thread_participants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('thread_id')->constrained('message_threads')->cascadeOnDelete();
            $t->string('name');
            $t->string('initials', 4)->nullable();
            $t->string('color', 24)->default('slate');
            $t->string('role')->nullable();
            $t->boolean('is_me')->default(false);
            $t->timestamps();
            $t->index(['thread_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thread_participants');
        Schema::table('message_threads', function (Blueprint $t) {
            $t->dropColumn(['is_group', 'group_name']);
        });
    }
};
