<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teacher_trainings', function (Blueprint $t) {
            $t->string('issuer')->nullable()->after('provider');
            $t->boolean('is_government_approved')->default(false)->after('issuer');
            $t->string('accreditation_no')->nullable()->after('is_government_approved');
            $t->string('country', 2)->default('ID')->after('accreditation_no');
            $t->unsignedSmallInteger('hours_certified')->nullable()->after('hours');
            $t->index(['teacher_id', 'is_government_approved']);
        });
    }

    public function down(): void
    {
        Schema::table('teacher_trainings', function (Blueprint $t) {
            $t->dropIndex(['teacher_id', 'is_government_approved']);
            $t->dropColumn(['issuer','is_government_approved','accreditation_no','country','hours_certified']);
        });
    }
};
