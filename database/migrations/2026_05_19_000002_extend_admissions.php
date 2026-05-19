<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollment_periods', function (Blueprint $t) {
            $t->decimal('pass_threshold', 5, 2)->default(70)->after('quota');
            $t->decimal('fail_threshold', 5, 2)->default(50)->after('pass_threshold');
            $t->string('exam_venue')->nullable()->after('fail_threshold');
            $t->dateTime('exam_starts_at')->nullable()->after('exam_venue');
            $t->text('exam_instructions')->nullable()->after('exam_starts_at');
        });

        Schema::table('applications', function (Blueprint $t) {
            $t->string('nisn', 20)->nullable()->after('current_school');
            $t->string('birthplace')->nullable()->after('dob');
            $t->string('address')->nullable()->after('parent_email');
            $t->string('city')->nullable()->after('address');
            $t->string('religion', 30)->nullable()->after('city');
            $t->string('ethnicity', 50)->nullable()->after('religion');
            $t->string('parent_whatsapp', 30)->nullable()->after('parent_phone');
            $t->string('parent_occupation')->nullable()->after('parent_email');
            $t->boolean('is_teacher_child')->default(false)->after('applicant_type');
            $t->boolean('is_existing_student')->default(false)->after('is_teacher_child');
            $t->string('existing_unit', 20)->nullable()->after('is_existing_student');
            $t->string('orphan_status', 20)->default('none')->after('existing_unit');
            $t->string('receipt_file')->nullable()->after('payment_status');
            $t->string('invoice_no')->nullable()->after('receipt_file');
            $t->string('payment_method', 20)->nullable()->after('invoice_no');
            $t->decimal('payment_amount', 12, 2)->default(300000)->after('payment_method');
            $t->dateTime('payment_paid_at')->nullable()->after('payment_amount');
            $t->string('assigned_temp_id')->nullable()->after('payment_paid_at');
            $t->string('assigned_student_no')->nullable()->after('assigned_temp_id');
            $t->string('decline_reason')->nullable()->after('assigned_student_no');
            $t->foreignId('enrollment_period_id')->nullable()->after('decline_reason')
                ->constrained('enrollment_periods')->nullOnDelete();
        });

        Schema::table('students', function (Blueprint $t) {
            $t->string('temporary_id')->nullable()->after('nis');
            $t->string('va_number', 40)->nullable()->after('temporary_id');
            $t->date('books_issued_at')->nullable()->after('enrolled_at');
            $t->date('account_activated_at')->nullable()->after('books_issued_at');
            $t->date('first_attendance_at')->nullable()->after('account_activated_at');
            $t->unsignedInteger('attendance_days_count')->default(0)->after('first_attendance_at');
            $t->string('cca_choice')->nullable()->after('attendance_days_count');
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_periods', function (Blueprint $t) {
            $t->dropColumn(['pass_threshold','fail_threshold','exam_venue','exam_starts_at','exam_instructions']);
        });
        Schema::table('applications', function (Blueprint $t) {
            $t->dropConstrainedForeignId('enrollment_period_id');
            $t->dropColumn([
                'nisn','birthplace','address','city','religion','ethnicity',
                'parent_whatsapp','parent_occupation','is_teacher_child','is_existing_student',
                'existing_unit','orphan_status','receipt_file','invoice_no','payment_method',
                'payment_amount','payment_paid_at','assigned_temp_id','assigned_student_no','decline_reason',
            ]);
        });
        Schema::table('students', function (Blueprint $t) {
            $t->dropColumn(['temporary_id','va_number','books_issued_at','account_activated_at','first_attendance_at','attendance_days_count','cca_choice']);
        });
    }
};
