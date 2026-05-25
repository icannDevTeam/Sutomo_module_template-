<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('duty_assignments', function (Blueprint $t) {
            $t->json('days_of_week')->nullable()->after('ends_at');
            $t->string('recurrence', 20)->default('once')->after('days_of_week'); // once / weekly
            $t->string('academic_year', 12)->nullable()->after('recurrence');     // e.g. 2025/2026
            $t->index(['academic_year']);
        });
    }

    public function down(): void
    {
        Schema::table('duty_assignments', function (Blueprint $t) {
            $t->dropIndex(['academic_year']);
            $t->dropColumn(['days_of_week', 'recurrence', 'academic_year']);
        });
    }
};
