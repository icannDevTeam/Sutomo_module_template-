<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lock the application fee + payment window into each EnrollmentPeriod so the
 * Set-Up Enrollment wizard (and the public /api/enrollment-config endpoint)
 * can serve a single source of truth to the front-end.
 *
 * Defaults: Rp 300.000 / 24 hours — matches Yayasan Sutomo's standing rule.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('enrollment_periods', function (Blueprint $t) {
            $t->unsignedInteger('application_fee')->default(300000)->after('quota');
            $t->unsignedSmallInteger('payment_expiry_hours')->default(24)->after('application_fee');
        });

        // Backfill any rows that pre-date this migration (idempotent).
        \DB::table('enrollment_periods')
            ->whereNull('application_fee')->orWhere('application_fee', 0)
            ->update(['application_fee' => 300000, 'payment_expiry_hours' => 24]);
    }

    public function down(): void
    {
        Schema::table('enrollment_periods', function (Blueprint $t) {
            $t->dropColumn(['application_fee', 'payment_expiry_hours']);
        });
    }
};
