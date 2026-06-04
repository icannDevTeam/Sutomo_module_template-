<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2: rename contract_handed_over_at -> agreement_signed_at on LOI,
 * and add signature fields. Locked decision: agreement letter e-sign IS the
 * handover event (single timestamp, no separate "handed over" step).
 */
return new class extends Migration {
    public function up(): void
    {
        // Step 1: add new columns.
        Schema::table('letters_of_intent', function (Blueprint $t) {
            $t->timestamp('agreement_signed_at')->nullable()->after('yayasan_contract_uploaded_at');
            $t->string('agreement_signature_text')->nullable()->after('agreement_signed_at');
            $t->string('agreement_signature_ip', 45)->nullable()->after('agreement_signature_text');
        });

        // Step 2: copy data from old column.
        DB::statement('UPDATE letters_of_intent SET agreement_signed_at = contract_handed_over_at WHERE contract_handed_over_at IS NOT NULL');

        // Step 3: drop old column.
        Schema::table('letters_of_intent', function (Blueprint $t) {
            $t->dropColumn('contract_handed_over_at');
        });
    }

    public function down(): void
    {
        Schema::table('letters_of_intent', function (Blueprint $t) {
            $t->timestamp('contract_handed_over_at')->nullable()->after('yayasan_contract_uploaded_at');
        });

        DB::statement('UPDATE letters_of_intent SET contract_handed_over_at = agreement_signed_at WHERE agreement_signed_at IS NOT NULL');

        Schema::table('letters_of_intent', function (Blueprint $t) {
            $t->dropColumn(['agreement_signed_at', 'agreement_signature_text', 'agreement_signature_ip']);
        });
    }
};
