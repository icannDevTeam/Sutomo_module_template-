<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // If downstream steps already happened, mark review as accepted for data consistency.
        DB::statement("\n            UPDATE letters_of_intent\n            SET\n                yayasan_review_status = 'accepted',\n                yayasan_review_notes = COALESCE(yayasan_review_notes, 'Backfilled from existing continuation milestones.'),\n                yayasan_reviewed_at = COALESCE(yayasan_reviewed_at, agreement_signed_at, buku_induk_recorded_at, continuation_completed_at, CURRENT_TIMESTAMP)\n            WHERE yayasan_review_status IS NULL\n              AND (agreement_signed_at IS NOT NULL OR buku_induk_recorded_at IS NOT NULL OR continuation_completed_at IS NOT NULL)\n        ");

        // If contract was uploaded but no review decision yet, keep it in uploaded state.
        DB::statement("\n            UPDATE letters_of_intent\n            SET yayasan_review_status = 'uploaded'\n            WHERE yayasan_review_status IS NULL\n              AND yayasan_contract_uploaded_at IS NOT NULL\n        ");

        // If submitted to Yayasan but no upload yet, mark awaiting upload.
        DB::statement("\n            UPDATE letters_of_intent\n            SET yayasan_review_status = 'awaiting_upload'\n            WHERE yayasan_review_status IS NULL\n              AND submitted_to_yayasan_at IS NOT NULL\n              AND yayasan_contract_uploaded_at IS NULL\n        ");
    }

    public function down(): void
    {
        // Backfill-only migration; no safe rollback.
    }
};
