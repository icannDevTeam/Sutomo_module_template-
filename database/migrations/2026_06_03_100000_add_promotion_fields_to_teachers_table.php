<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->string('promotion_readiness', 20)->nullable()->after('last_review');
            $t->text('promotion_readiness_note')->nullable()->after('promotion_readiness');
            $t->unsignedBigInteger('promotion_readiness_set_by')->nullable()->after('promotion_readiness_note');
            $t->timestamp('promotion_readiness_set_at')->nullable()->after('promotion_readiness_set_by');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->dropColumn([
                'promotion_readiness',
                'promotion_readiness_note',
                'promotion_readiness_set_by',
                'promotion_readiness_set_at',
            ]);
        });
    }
};
