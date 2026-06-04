<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3: singleton settings table for the Observation / Probation Watch
 * configuration. Always exactly one row (id=1). Read via ObservationSetting::current().
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('observation_settings', function (Blueprint $t) {
            $t->id();

            // Probation Watch
            $t->boolean('probation_watch_enabled')->default(true);
            $t->unsignedSmallInteger('probation_min_supervisions')->default(4);
            $t->unsignedSmallInteger('probation_min_peer_observations')->default(2);
            $t->unsignedSmallInteger('probation_decision_due_days')->default(14); // window before probation_ends_at to flag "decision due"

            // Contract renewal (forward compat — Phase 4 will use this)
            $t->unsignedSmallInteger('renewal_window_months')->default(3);

            $t->timestamps();
        });

        DB::table('observation_settings')->insert([
            'id'                              => 1,
            'probation_watch_enabled'         => true,
            'probation_min_supervisions'      => 4,
            'probation_min_peer_observations' => 2,
            'probation_decision_due_days'     => 14,
            'renewal_window_months'           => 3,
            'created_at'                      => now(),
            'updated_at'                      => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_settings');
    }
};
