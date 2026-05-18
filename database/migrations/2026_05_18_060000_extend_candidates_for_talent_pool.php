<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $t) {
            $t->string('qualification')->nullable()->after('education'); // S1 / S2 / S3 / Diploma
            $t->string('source')->default('website')->after('priority'); // website, walk-in, referral, agency, fair
            $t->boolean('talent_pool')->default(true)->after('source');  // in pool even if no vacancy
            $t->boolean('shortlisted')->default(false)->after('talent_pool');
            $t->string('availability')->nullable()->after('shortlisted'); // immediate / 1-month / 2026-07-01
            $t->unsignedInteger('desired_salary')->nullable()->after('availability');
            $t->string('current_school')->nullable()->after('desired_salary');
            $t->json('past_schools')->nullable()->after('current_school');
            $t->json('certifications')->nullable()->after('past_schools');
            $t->json('languages')->nullable()->after('certifications');
            $t->string('cv_url')->nullable()->after('languages');
            $t->text('notes')->nullable()->after('cv_url');
            $t->string('preferred_campus')->nullable()->after('notes'); // sd/smp/sma/int
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $t) {
            $t->dropColumn([
                'qualification','source','talent_pool','shortlisted',
                'availability','desired_salary','current_school','past_schools',
                'certifications','languages','cv_url','notes','preferred_campus',
            ]);
        });
    }
};
