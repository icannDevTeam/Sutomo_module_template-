<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enrollment_periods', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('campus');
            $t->string('unit');
            $t->date('opens_at');
            $t->date('closes_at');
            $t->string('status')->default('open');
            $t->unsignedSmallInteger('quota')->default(0);
            $t->timestamps();
        });

        Schema::create('school_classes', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('campus');
            $t->string('grade');
            $t->string('stream')->nullable();
            $t->string('room')->nullable();
            $t->foreignId('homeroom_teacher_id')->nullable();
            $t->unsignedSmallInteger('capacity')->default(28);
            $t->timestamps();
        });

        Schema::create('students', function (Blueprint $t) {
            $t->id();
            $t->string('nis')->unique();
            $t->string('name');
            $t->string('gender', 1)->nullable();
            $t->date('dob')->nullable();
            $t->string('religion')->nullable();
            $t->string('ethnicity')->nullable();
            $t->string('city')->nullable();
            $t->string('campus');
            $t->string('unit')->nullable();
            $t->string('grade')->nullable();
            $t->string('stream')->nullable();
            $t->foreignId('school_class_id')->nullable();
            $t->string('status')->default('active');
            $t->date('enrolled_at')->nullable();
            $t->string('parent_name')->nullable();
            $t->string('parent_phone')->nullable();
            $t->string('parent_email')->nullable();
            $t->unsignedTinyInteger('attendance_rate')->nullable();
            $t->decimal('gpa', 4, 2)->nullable();
            $t->string('fee_status')->default('paid');
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        Schema::create('applications', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('gender', 1)->nullable();
            $t->date('dob')->nullable();
            $t->string('parent_name')->nullable();
            $t->string('parent_phone')->nullable();
            $t->string('parent_email')->nullable();
            $t->string('current_school')->nullable();
            $t->string('campus');
            $t->string('unit');
            $t->string('grade')->nullable();
            $t->string('stream')->nullable();
            $t->string('applicant_type')->default('new');
            $t->string('status')->default('submitted');
            $t->date('applied_at')->nullable();
            $t->date('exam_date')->nullable();
            $t->unsignedTinyInteger('placement_score')->nullable();
            $t->string('placement_recommendation')->nullable();
            $t->string('payment_status')->default('pending');
            $t->boolean('waitlisted')->default(false);
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        Schema::create('behavior_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('teacher_id')->nullable();
            $t->date('occurred_at');
            $t->string('category');
            $t->string('severity')->default('low');
            $t->string('title');
            $t->text('notes')->nullable();
            $t->string('status')->default('open');
            $t->string('action_taken')->nullable();
            $t->timestamps();
        });

        Schema::create('teacher_leaves', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->string('type');
            $t->date('starts_at');
            $t->date('ends_at');
            $t->text('reason')->nullable();
            $t->string('status')->default('pending');
            $t->foreignId('substitute_teacher_id')->nullable();
            $t->string('decided_by')->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->timestamps();
        });

        Schema::create('ssc_requests', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->string('type');
            $t->string('priority')->default('normal');
            $t->string('status')->default('pending');
            $t->json('clearance')->nullable();
            $t->text('notes')->nullable();
            $t->date('requested_at');
            $t->timestamps();
        });

        Schema::create('procurement_requests', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('category');
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('campus');
            $t->unsignedInteger('amount')->default(0);
            $t->string('status')->default('pending');
            $t->string('requested_by')->nullable();
            $t->date('needed_by')->nullable();
            $t->timestamps();
        });

        Schema::create('school_events', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('title');
            $t->string('category');
            $t->string('campus');
            $t->date('starts_at');
            $t->date('ends_at')->nullable();
            $t->string('pic')->nullable();
            $t->string('status')->default('draft');
            $t->unsignedSmallInteger('participants')->default(0);
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('supervisi_evaluations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->date('scheduled_at');
            $t->string('evaluator')->nullable();
            $t->string('round');
            $t->unsignedTinyInteger('score')->nullable();
            $t->string('status')->default('scheduled');
            $t->text('strengths')->nullable();
            $t->text('improvements')->nullable();
            $t->string('training_recommended')->nullable();
            $t->timestamps();
        });

        Schema::table('users', function (Blueprint $t) {
            if (! Schema::hasColumn('users', 'role'))   $t->string('role')->default('admin')->after('email');
            if (! Schema::hasColumn('users', 'campus')) $t->string('campus')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        foreach ([
            'supervisi_evaluations','school_events','procurement_requests',
            'ssc_requests','teacher_leaves','behavior_logs',
            'applications','students','school_classes','enrollment_periods',
        ] as $tbl) Schema::dropIfExists($tbl);

        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users', 'role'))   $t->dropColumn('role');
            if (Schema::hasColumn('users', 'campus')) $t->dropColumn('campus');
        });
    }
};
