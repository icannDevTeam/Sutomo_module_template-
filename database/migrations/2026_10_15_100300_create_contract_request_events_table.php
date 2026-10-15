<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only event log for the full lifecycle of a contract approval request.
 *
 * actor_name is denormalized so audit history survives account deletion.
 * Never update or delete rows in this table.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('contract_request_events', function (Blueprint $t) {
            $t->id();

            $t->foreignId('request_id')
              ->constrained('contract_approval_requests')
              ->cascadeOnDelete();

            // Event type constants — see ContractRequestEvent::TYPES.
            $t->string('event_type', 48);

            // Actor — nullable in case of system events.
            $t->unsignedBigInteger('actor_id')->nullable();
            $t->string('actor_name')->nullable();  // denormalized
            $t->string('actor_role', 24)->nullable();

            // Optional links to revision / document at the time of the event.
            $t->unsignedBigInteger('revision_id')->nullable();
            $t->unsignedBigInteger('document_version_id')->nullable();

            // Stage transition recorded for easy querying.
            $t->string('from_stage', 32)->nullable();
            $t->string('to_stage', 32)->nullable();

            // Human-readable note or reason.
            $t->text('note')->nullable();

            $t->timestamp('occurred_at');

            $t->timestamps();

            $t->index(['request_id', 'occurred_at']);
            $t->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_request_events');
    }
};
