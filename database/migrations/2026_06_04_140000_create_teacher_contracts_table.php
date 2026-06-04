<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 spine: teacher_contracts.
 *
 * Owns the full lifecycle of a single contract instance for a teacher:
 *   PKWT-I (probation) → PKWT-II → PKWT-III → Guru SK (permanent).
 *
 * One Teacher has many TeacherContracts over time. The most recent open
 * contract is the teacher's "current" contract. Every LOI links to exactly
 * one TeacherContract via letters_of_intent.teacher_contract_id.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_contracts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();

            // Contract type & period.
            $t->string('type', 16); // pkwt_1 | pkwt_2 | pkwt_3 | guru_sk
            $t->string('academic_year');         // e.g. "2025/2026"
            $t->date('starts_at');
            $t->date('ends_at')->nullable();     // null for guru_sk (permanent)

            // Probation (PKWT-I only; 3 months from start).
            $t->date('probation_starts_at')->nullable();
            $t->date('probation_ends_at')->nullable();
            $t->string('probation_recommendation_path')->nullable();
            $t->string('probation_decision', 24)->nullable(); // continue | terminate | refund_triggered
            $t->timestamp('probation_decision_at')->nullable();
            $t->timestamp('commitment_fee_refund_triggered_at')->nullable();

            // Contract document (the signed PKWT/SK PDF).
            $t->string('contract_file_path')->nullable();
            $t->timestamp('signed_at')->nullable();
            $t->string('signature_text')->nullable();
            $t->string('signature_ip', 45)->nullable();

            // Renewal window (3 months before ends_at, configurable).
            $t->date('renewal_window_starts_at')->nullable();
            $t->timestamp('renewal_notified_at')->nullable();
            $t->string('renewal_form_path')->nullable();
            $t->timestamp('renewal_form_submitted_at')->nullable();

            // Recommendation letter (principal → yayasan).
            $t->string('recommendation_letter_path')->nullable();
            $t->string('recommendation_decision', 24)->nullable(); // renew | not_renew

            // Yayasan handoff.
            $t->timestamp('submitted_to_yayasan_at')->nullable();
            $t->timestamp('yayasan_response_at')->nullable();
            $t->string('yayasan_decision', 24)->nullable(); // approved | rejected

            // SK letter (Guru SK promotion).
            $t->string('sk_letter_path')->nullable();
            $t->timestamp('sk_issued_at')->nullable();

            // Agreement letter (e-sign by teacher = handover event).
            $t->string('agreement_letter_path')->nullable();
            $t->timestamp('agreement_signed_at')->nullable();
            $t->string('agreement_signature_text')->nullable();
            $t->string('agreement_signature_ip', 45)->nullable();

            // Buku Induk + closure.
            $t->timestamp('buku_induk_recorded_at')->nullable();
            $t->timestamp('continuation_completed_at')->nullable();

            // Lifecycle status (computed-friendly).
            $t->string('status', 24)->default('active'); // active | renewed | expired | terminated | not_renewed | resigned

            $t->timestamps();

            $t->index(['teacher_id', 'status']);
            $t->index(['type', 'status']);
            $t->index('ends_at');
            $t->index('renewal_window_starts_at');
            $t->index('probation_ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_contracts');
    }
};
