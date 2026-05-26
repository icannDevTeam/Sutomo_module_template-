<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teacher_observations', function (Blueprint $t) {
            if (! Schema::hasColumn('teacher_observations', 'notes_by_criterion')) {
                $t->json('notes_by_criterion')->nullable()->after('dimensions');
            }
            if (! Schema::hasColumn('teacher_observations', 'status')) {
                $t->string('status', 32)->default('pending')->after('follow_up_date');
            }
            if (! Schema::hasColumn('teacher_observations', 'reviewed_by')) {
                $t->foreignId('reviewed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('teacher_observations', 'reviewed_at')) {
                $t->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (! Schema::hasColumn('teacher_observations', 'review_notes')) {
                $t->text('review_notes')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_observations', function (Blueprint $t) {
            if (Schema::hasColumn('teacher_observations', 'reviewed_by')) {
                $t->dropConstrainedForeignId('reviewed_by');
            }
            foreach (['notes_by_criterion', 'status', 'reviewed_at', 'review_notes'] as $col) {
                if (Schema::hasColumn('teacher_observations', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
