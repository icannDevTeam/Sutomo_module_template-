<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registry of school payment accounts (bank / virtual account) used by
 * the onboarding flow. Scoped per campus and purpose so the same UI can
 * later cover dev_fee and tuition without schema changes.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('campus', 20);          // CMP-001 / CMP-002 / CMP-003
            $t->string('purpose', 20)->default('books'); // books | devfee | tuition
            $t->string('bank_name');
            $t->string('account_name');
            $t->string('account_no', 40);
            $t->string('va_prefix', 20)->nullable();
            $t->boolean('is_active')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['campus', 'purpose', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};
