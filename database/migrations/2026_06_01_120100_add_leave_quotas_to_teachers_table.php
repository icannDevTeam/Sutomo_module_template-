<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->unsignedSmallInteger('annual_quota')->default(12)->after('employment');
            $t->unsignedSmallInteger('sick_quota')->default(12)->after('annual_quota');
            $t->unsignedSmallInteger('personal_quota')->default(5)->after('sick_quota');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->dropColumn(['annual_quota', 'sick_quota', 'personal_quota']);
        });
    }
};
