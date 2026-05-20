<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-application Virtual Account fields. The VA itself is issued by the
 * Unit Head from the Open Enrollment hub once they have the bank-side number;
 * the system only validates format and computes expiry from the period config.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $t) {
            $t->string('va_number', 40)->nullable()->after('payment_paid_at');
            $t->string('va_purpose', 20)->default('enrollment')->after('va_number');
            $t->dateTime('va_issued_at')->nullable()->after('va_purpose');
            $t->string('va_issued_by')->nullable()->after('va_issued_at');
            $t->dateTime('va_expires_at')->nullable()->after('va_issued_by');
            $t->dateTime('submitted_at')->nullable()->after('va_expires_at');

            $t->index(['status', 'va_expires_at'], 'applications_status_va_expires_idx');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $t) {
            $t->dropIndex('applications_status_va_expires_idx');
            $t->dropColumn(['va_number', 'va_purpose', 'va_issued_at', 'va_issued_by', 'va_expires_at', 'submitted_at']);
        });
    }
};
