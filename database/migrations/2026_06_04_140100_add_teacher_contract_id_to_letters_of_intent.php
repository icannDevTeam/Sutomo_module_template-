<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1: link every LOI to its TeacherContract spine row.
 * Nullable so existing LOIs survive; backfill seeder populates this.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('letters_of_intent', function (Blueprint $t) {
            $t->foreignId('teacher_contract_id')
                ->nullable()
                ->after('teacher_id')
                ->constrained('teacher_contracts')
                ->nullOnDelete();
            $t->index('teacher_contract_id', 'loi_teacher_contract_idx');
        });
    }

    public function down(): void
    {
        Schema::table('letters_of_intent', function (Blueprint $t) {
            $t->dropIndex('loi_teacher_contract_idx');
            $t->dropForeign(['teacher_contract_id']);
            $t->dropColumn('teacher_contract_id');
        });
    }
};
