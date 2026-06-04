<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('letters_of_intent', function (Blueprint $table) {
            $table->timestamp('submitted_to_yayasan_at')->nullable();
            $table->string('yayasan_contract_path')->nullable();
            $table->timestamp('yayasan_contract_uploaded_at')->nullable();
            $table->timestamp('contract_handed_over_at')->nullable();
            $table->timestamp('buku_induk_recorded_at')->nullable();
            $table->timestamp('continuation_completed_at')->nullable();

            $table->index('submitted_to_yayasan_at', 'loi_submitted_to_yayasan_idx');
            $table->index('continuation_completed_at', 'loi_continuation_completed_idx');
        });
    }

    public function down(): void
    {
        Schema::table('letters_of_intent', function (Blueprint $table) {
            $table->dropIndex('loi_submitted_to_yayasan_idx');
            $table->dropIndex('loi_continuation_completed_idx');

            $table->dropColumn([
                'submitted_to_yayasan_at',
                'yayasan_contract_path',
                'yayasan_contract_uploaded_at',
                'contract_handed_over_at',
                'buku_induk_recorded_at',
                'continuation_completed_at',
            ]);
        });
    }
};