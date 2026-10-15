<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable snapshots of what the principal submitted for Yayasan to review.
 *
 * Each correction produces a new revision (monotonically increasing revision_number).
 * Approval is pinned to a specific revision so the approved terms are preserved.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('contract_request_revisions', function (Blueprint $t) {
            $t->id();

            $t->foreignId('request_id')
              ->constrained('contract_approval_requests')
              ->cascadeOnDelete();

            $t->unsignedTinyInteger('revision_number')->default(1);

            // Recommendation and justification from the principal.
            $t->string('recommendation', 24)->nullable(); // renew | not_renew | continue | withdraw
            $t->text('justification')->nullable();

            // Contract tier details (for pkwt_renewal).
            $t->string('current_type', 16)->nullable();   // pkwt_1 | pkwt_2 | pkwt_3
            $t->string('proposed_type', 16)->nullable();  // pkwt_2 | pkwt_3 | guru_sk
            $t->date('proposed_starts_at')->nullable();
            $t->date('proposed_ends_at')->nullable();

            // Academic year context.
            $t->string('academic_year')->nullable();

            // Evidence files uploaded at submission time (JSON array of storage paths).
            $t->json('evidence_paths')->nullable();

            // Who submitted this revision and when.
            $t->unsignedBigInteger('submitted_by');
            $t->timestamp('submitted_at');

            $t->timestamps();

            $t->unique(['request_id', 'revision_number']);
            $t->index('request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_request_revisions');
    }
};
