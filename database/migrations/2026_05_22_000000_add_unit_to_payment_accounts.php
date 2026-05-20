<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add the `unit` column so payment accounts can be looked up by both
 * campus and unit (the unit digit drives the canonical Sutomo VA prefix).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_accounts', function (Blueprint $t) {
            $t->string('unit', 20)->nullable()->after('campus');
            $t->index(['campus', 'unit', 'purpose', 'is_active'], 'payment_accounts_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payment_accounts', function (Blueprint $t) {
            $t->dropIndex('payment_accounts_lookup_idx');
            $t->dropColumn('unit');
        });
    }
};
