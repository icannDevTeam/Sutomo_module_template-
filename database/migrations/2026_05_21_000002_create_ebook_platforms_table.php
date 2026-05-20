<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lookup of e-learning platforms (Quipper, Ruangguru, Pijar Sekolah…)
 * referenced by EbookPack items so the catalog stays normalized.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('ebook_platforms', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('base_url');
            $t->string('logo_url')->nullable();
            $t->boolean('is_active')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebook_platforms');
    }
};
