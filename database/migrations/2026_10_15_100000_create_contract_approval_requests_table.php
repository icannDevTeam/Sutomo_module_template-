<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central spine for the Yayasan Approval Portal.
 *
 * Each row = one principal submission for Yayasan review.
 * Covers both PKWT contract renewals and Guru SK annual continuations.
 * Hiring candidate approvals remain in the candidates pipeline.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('contract_approval_requests', function (Blueprint $t) {
            $t->id();

            // What kind of request this is.
            $t->string('type', 24); // pkwt_renewal | annual_continuation

            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();

            // Source record — mutually exclusive depending on type.
            $t->unsignedBigInteger('source_contract_id')->nullable(); // FK → teacher_contracts
            $t->unsignedBigInteger('source_loi_id')->nullable();      // FK → letters_of_intent

            // Who submitted and when, plus organizational scope at that moment.
            $t->unsignedBigInteger('submitted_by')->nullable();
            $t->string('campus')->nullable();
            $t->timestamp('submitted_at')->nullable();

            // Current workflow stage.
            $t->string('stage', 32)->default('draft');
            // Stages: draft | submitted | under_review | correction_requested |
            //         approved | rejected | hr_assigned | hr_in_progress |
            //         document_uploaded | document_correction | document_accepted |
            //         signed_scheduled | hr_registration | completed | cancelled

            // Yayasan review.
            $t->timestamp('review_started_at')->nullable();
            $t->unsignedBigInteger('review_started_by')->nullable();

            // Decision.
            $t->string('decision', 16)->nullable();    // approved | rejected
            $t->timestamp('decided_at')->nullable();
            $t->unsignedBigInteger('decided_by')->nullable();
            $t->text('decision_reason')->nullable();

            // Approved revision reference (set at approval time).
            $t->unsignedBigInteger('approved_revision_id')->nullable();

            // HR assignment.
            $t->unsignedBigInteger('assigned_hr_id')->nullable();
            $t->timestamp('hr_assigned_at')->nullable();
            $t->unsignedBigInteger('hr_assigned_by')->nullable();
            $t->text('hr_instructions')->nullable();
            $t->date('hr_preparation_due_at')->nullable();

            // Resulting contract (populated when a new PKWT contract is spawned).
            $t->unsignedBigInteger('resulting_contract_id')->nullable();

            // Completion.
            $t->timestamp('completed_at')->nullable();

            // Legacy / backfill provenance.
            $t->boolean('is_legacy')->default(false);
            $t->text('legacy_note')->nullable();

            $t->timestamps();

            $t->index(['stage', 'type']);
            $t->index('teacher_id');
            $t->index('submitted_by');
            $t->index('assigned_hr_id');
            $t->index('source_contract_id');
            $t->index('source_loi_id');
            $t->index('decided_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_approval_requests');
    }
};
