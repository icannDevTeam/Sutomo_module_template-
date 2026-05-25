<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->foreignId('mentor_id')->nullable()->after('title')->constrained('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $t) {
            $t->dropConstrainedForeignId('mentor_id');
        });
    }
};
