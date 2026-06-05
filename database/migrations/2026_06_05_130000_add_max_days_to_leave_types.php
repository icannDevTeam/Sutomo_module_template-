<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $t) {
            $t->unsignedSmallInteger('max_days')->nullable()->after('requires_substitute');
        });

        \DB::table('leave_types')->where('key', 'marriage')->update(['max_days' => 7]);
        \DB::table('leave_types')->where('key', 'bereavement')->update(['max_days' => 4]);
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $t) {
            $t->dropColumn('max_days');
        });
    }
};
