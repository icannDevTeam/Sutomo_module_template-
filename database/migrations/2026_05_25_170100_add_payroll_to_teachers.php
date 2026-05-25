<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->string('bank_name')->nullable()->after('city');
            $t->text('bank_account_no')->nullable()->after('bank_name'); // encrypted
            $t->text('tax_id_npwp')->nullable()->after('bank_account_no'); // encrypted
            $t->string('payroll_id')->nullable()->after('tax_id_npwp');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->dropColumn(['bank_name', 'bank_account_no', 'tax_id_npwp', 'payroll_id']);
        });
    }
};
