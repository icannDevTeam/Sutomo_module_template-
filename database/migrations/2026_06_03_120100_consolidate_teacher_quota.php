<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->unsignedSmallInteger('quota')->default(12)->after('substitution_category_set_at');
        });

        // Migrate existing data: copy max(annual_quota, sick_quota, personal_quota) → quota
        if (Schema::hasColumn('teachers', 'annual_quota')) {
            \DB::statement("UPDATE teachers SET quota = COALESCE(annual_quota, 12)");
        }

        Schema::table('teachers', function (Blueprint $t) {
            if (Schema::hasColumn('teachers', 'annual_quota')) $t->dropColumn('annual_quota');
            if (Schema::hasColumn('teachers', 'sick_quota')) $t->dropColumn('sick_quota');
            if (Schema::hasColumn('teachers', 'personal_quota')) $t->dropColumn('personal_quota');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->unsignedSmallInteger('annual_quota')->default(12)->after('quota');
            $t->unsignedSmallInteger('sick_quota')->default(12)->after('annual_quota');
            $t->unsignedSmallInteger('personal_quota')->default(5)->after('sick_quota');
        });

        \DB::statement("UPDATE teachers SET annual_quota = quota, sick_quota = quota, personal_quota = quota");

        Schema::table('teachers', function (Blueprint $t) {
            $t->dropColumn('quota');
        });
    }
};
