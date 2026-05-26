<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            if (! Schema::hasColumn('teachers', 'is_unit_head')) {
                $t->boolean('is_unit_head')->default(false)->after('subject');
            }
            if (! Schema::hasColumn('teachers', 'unit_head_subject')) {
                $t->string('unit_head_subject')->nullable()->after('is_unit_head');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            if (Schema::hasColumn('teachers', 'unit_head_subject')) {
                $t->dropColumn('unit_head_subject');
            }
            if (Schema::hasColumn('teachers', 'is_unit_head')) {
                $t->dropColumn('is_unit_head');
            }
        });
    }
};
