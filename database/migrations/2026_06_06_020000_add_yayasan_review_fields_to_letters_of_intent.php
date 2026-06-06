<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('letters_of_intent', function (Blueprint $table) {
            $table->string('yayasan_review_status', 32)->nullable()->after('yayasan_contract_uploaded_at');
            $table->text('yayasan_review_notes')->nullable()->after('yayasan_review_status');
            $table->text('yayasan_resubmit_notes')->nullable()->after('yayasan_review_notes');
            $table->unsignedBigInteger('yayasan_reviewed_by')->nullable()->after('yayasan_resubmit_notes');
            $table->timestamp('yayasan_reviewed_at')->nullable()->after('yayasan_reviewed_by');

            $table->index('yayasan_review_status', 'loi_yayasan_review_status_idx');
            $table->index('yayasan_reviewed_at', 'loi_yayasan_reviewed_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('letters_of_intent', function (Blueprint $table) {
            $table->dropIndex('loi_yayasan_review_status_idx');
            $table->dropIndex('loi_yayasan_reviewed_at_idx');

            $table->dropColumn([
                'yayasan_review_status',
                'yayasan_review_notes',
                'yayasan_resubmit_notes',
                'yayasan_reviewed_by',
                'yayasan_reviewed_at',
            ]);
        });
    }
};
