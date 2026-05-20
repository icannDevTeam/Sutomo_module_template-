<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->longText('body');
            $t->string('author_name');
            $t->string('author_role')->nullable();           // Principal, HR, Coordinator, etc.
            $t->json('audiences');                            // ['all_parents','sd','smp','sma','staff','grade_10', ...]
            $t->json('channels');                             // ['app','email','whatsapp']
            $t->string('category')->default('general');       // general, academic, event, urgent, reminder
            $t->string('status')->default('draft');           // draft | scheduled | sent | archived
            $t->boolean('pinned')->default(false);
            $t->dateTime('scheduled_at')->nullable();
            $t->dateTime('sent_at')->nullable();
            $t->unsignedInteger('read_count')->default(0);
            $t->unsignedInteger('recipient_count')->default(0);
            $t->json('attachments')->nullable();
            $t->timestamps();
            $t->index(['status', 'sent_at']);
            $t->index(['pinned', 'sent_at']);
        });

        Schema::create('announcement_reads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('user_id');
            $t->dateTime('read_at');
            $t->timestamps();
            $t->unique(['announcement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcements');
    }
};
