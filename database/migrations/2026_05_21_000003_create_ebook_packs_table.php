<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog of e-Book packs per (unit, grade, school_year). items is a JSON
 * list of {title, platform_id, url, login_hint, notes} pointing at the
 * ebook_platforms registry.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('ebook_packs', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('unit', 20);
            $t->string('grade', 10)->nullable();
            $t->string('school_year', 20);
            $t->json('items');
            $t->foreignId('default_platform_id')->nullable()->constrained('ebook_platforms')->nullOnDelete();
            $t->boolean('is_active')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['unit', 'grade', 'school_year'], 'ebook_packs_cohort_unique');
            $t->index(['unit', 'grade', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebook_packs');
    }
};
