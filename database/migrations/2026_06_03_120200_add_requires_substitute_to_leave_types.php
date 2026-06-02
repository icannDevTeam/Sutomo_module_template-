<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $t) {
            $t->boolean('requires_substitute')->default(true)->after('affects_quota');
        });

        // Brief Absence and Assigned Work do not require a substitute by default.
        \DB::table('leave_types')
            ->whereIn('key', ['brief_absence', 'assigned_work'])
            ->update(['requires_substitute' => false]);
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $t) {
            $t->dropColumn('requires_substitute');
        });
    }
};
