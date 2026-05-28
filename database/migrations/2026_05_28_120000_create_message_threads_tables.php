<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('message_threads', function (Blueprint $t) {
            $t->id();
            $t->string('subject');                          // "Regarding Dian's absences"
            $t->string('category', 16)->default('parent');  // parent | students | staff
            $t->string('partner_name');                     // "Budi Pratiwi"
            $t->string('partner_role')->nullable();         // "Parent · Dian Pratiwi (4A)"
            $t->string('partner_initials', 4)->nullable();  // "BP"
            $t->string('partner_color', 24)->default('emerald');
            $t->timestamp('last_message_at')->nullable();
            $t->unsignedInteger('unread_count')->default(0);
            $t->timestamps();
            $t->index(['category', 'last_message_at']);
        });

        Schema::create('thread_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('thread_id')->constrained('message_threads')->cascadeOnDelete();
            $t->boolean('is_me')->default(false);           // true => sent by current Principal user
            $t->string('sender_name');
            $t->string('sender_initials', 4)->nullable();
            $t->text('body');
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
            $t->index(['thread_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thread_messages');
        Schema::dropIfExists('message_threads');
    }
};
