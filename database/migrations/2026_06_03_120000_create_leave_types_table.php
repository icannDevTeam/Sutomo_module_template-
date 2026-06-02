<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $t) {
            $t->id();
            $t->string('key', 32)->unique();         // slug used on TeacherLeave.type
            $t->string('label');                     // human label
            $t->text('description')->nullable();
            $t->boolean('affects_quota')->default(true);
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->string('color', 16)->default('gray'); // filament color name
            $t->string('icon', 64)->nullable();      // heroicon-o-*
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
