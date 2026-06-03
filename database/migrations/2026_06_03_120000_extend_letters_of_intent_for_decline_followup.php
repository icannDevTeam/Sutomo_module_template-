<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('letters_of_intent', function (Blueprint $table) {
            $table->string('follow_up_status')->nullable();
            $table->string('follow_up_outcome')->nullable();
            $table->text('meeting_notes')->nullable();
            $table->text('actions_taken')->nullable();
            $table->string('resignation_letter_path')->nullable();
            $table->timestamp('resignation_letter_uploaded_at')->nullable();
            $table->string('hr_handoff_status')->nullable();
            $table->timestamp('hr_handoff_marked_at')->nullable();
            $table->timestamp('hr_uploaded_at')->nullable();
            $table->timestamp('last_reminder_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            $table->index(['academic_year', 'archived_at'], 'loi_ay_archived_idx');
            $table->index('follow_up_status', 'loi_follow_up_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('letters_of_intent', function (Blueprint $table) {
            $table->dropIndex('loi_ay_archived_idx');
            $table->dropIndex('loi_follow_up_status_idx');

            $table->dropColumn([
                'follow_up_status',
                'follow_up_outcome',
                'meeting_notes',
                'actions_taken',
                'resignation_letter_path',
                'resignation_letter_uploaded_at',
                'hr_handoff_status',
                'hr_handoff_marked_at',
                'hr_uploaded_at',
                'last_reminder_at',
                'archived_at',
            ]);
        });
    }
};
