<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->string('substitution_category', 20)->default('standard')->index()->after('promotion_readiness_set_at');
            $t->text('substitution_category_note')->nullable()->after('substitution_category');
            $t->unsignedBigInteger('substitution_category_set_by')->nullable()->after('substitution_category_note');
            $t->timestamp('substitution_category_set_at')->nullable()->after('substitution_category_set_by');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->dropIndex(['substitution_category']);
            $t->dropColumn([
                'substitution_category',
                'substitution_category_note',
                'substitution_category_set_by',
                'substitution_category_set_at',
            ]);
        });
    }
};
