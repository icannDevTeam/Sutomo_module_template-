<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog of physical book packages a Principal/Finance team configures
 * for each (unit, grade, school_year) cohort. Each row stores a JSON list
 * of line-items so the demo can show an invoice-style breakdown.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('book_packages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('unit', 20);            // sd, smp, sma, tk, playgroup, pre_nursery
            $t->string('grade', 10)->nullable(); // "1", "7", "10", or NULL for unit-wide
            $t->string('school_year', 20);     // "2026/2027"
            $t->json('items');                 // [{title, qty:int, unit_price:int}]
            $t->unsignedInteger('total')->default(0); // denormalized; recomputed on save
            $t->boolean('is_active')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['unit', 'grade', 'school_year'], 'book_packages_cohort_unique');
            $t->index(['unit', 'grade', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_packages');
    }
};
