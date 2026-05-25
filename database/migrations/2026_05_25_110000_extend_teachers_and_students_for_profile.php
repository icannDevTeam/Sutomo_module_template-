<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            if (!Schema::hasColumn('teachers', 'awards')) {
                $t->json('awards')->nullable()->after('certifications');
            }
            if (!Schema::hasColumn('teachers', 'initiatives')) {
                $t->json('initiatives')->nullable()->after('awards');
            }
            if (!Schema::hasColumn('teachers', 'children_quota')) {
                $t->unsignedTinyInteger('children_quota')->nullable()->after('initiatives')
                    ->comment('Allowed number of children with tuition subsidy benefit');
            }
        });

        Schema::create('teacher_trainings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('provider')->nullable();
            $t->string('category')->nullable(); // pedagogy / subject / leadership / wellness / tech
            $t->date('starts_on')->nullable();
            $t->date('ends_on')->nullable();
            $t->unsignedSmallInteger('hours')->nullable();
            $t->string('certificate_path')->nullable();
            $t->string('status')->default('completed'); // planned / in_progress / completed
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['teacher_id', 'starts_on']);
        });

        Schema::table('students', function (Blueprint $t) {
            if (!Schema::hasColumn('students', 'parent_teacher_id')) {
                $t->foreignId('parent_teacher_id')->nullable()->after('id')
                    ->constrained('teachers')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $t) {
            if (Schema::hasColumn('students', 'parent_teacher_id')) {
                $t->dropConstrainedForeignId('parent_teacher_id');
            }
        });

        Schema::dropIfExists('teacher_trainings');

        Schema::table('teachers', function (Blueprint $t) {
            foreach (['awards', 'initiatives', 'children_quota'] as $c) {
                if (Schema::hasColumn('teachers', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
