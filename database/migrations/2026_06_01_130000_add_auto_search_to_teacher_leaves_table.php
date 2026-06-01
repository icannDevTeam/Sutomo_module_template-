<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_leaves', function (Blueprint $t) {
            $t->boolean('auto_search_enabled')->default(true)->after('substitute_teacher_id');
            // disabled | open | closed_full | closed_manual | closed_assigned | expired
            $t->string('auto_search_status')->default('disabled')->after('auto_search_enabled');
            $t->timestamp('auto_search_opened_at')->nullable()->after('auto_search_status');
            $t->timestamp('auto_search_closes_at')->nullable()->after('auto_search_opened_at');
            $t->timestamp('auto_search_closed_at')->nullable()->after('auto_search_closes_at');
            $t->unsignedSmallInteger('auto_search_cap')->default(5)->after('auto_search_closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_leaves', function (Blueprint $t) {
            $t->dropColumn([
                'auto_search_enabled',
                'auto_search_status',
                'auto_search_opened_at',
                'auto_search_closes_at',
                'auto_search_closed_at',
                'auto_search_cap',
            ]);
        });
    }
};
