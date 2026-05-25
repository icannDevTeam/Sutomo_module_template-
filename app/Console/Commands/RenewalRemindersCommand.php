<?php

namespace App\Console\Commands;

use App\Models\Teacher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RenewalRemindersCommand extends Command
{
    protected $signature = 'teachers:renewal-reminders';
    protected $description = 'Surface contract renewal reminders at 90/60/30 days before contract_end';

    public function handle(): int
    {
        $today = now()->startOfDay();
        $windows = [90, 60, 30];
        $created = 0;

        foreach ($windows as $d) {
            $target = $today->copy()->addDays($d);
            $rows = Teacher::whereDate('contract_end', $target)->get();
            foreach ($rows as $t) {
                DB::table('audit_logs')->insert([
                    'occurred_at' => now(),
                    'user_name'   => 'system',
                    'role'        => 'system',
                    'action'      => 'renewal_reminder',
                    'target'      => 'Teacher:'.$t->id,
                    'note'        => "Contract ends in {$d} days ({$t->contract_end->format('d M Y')}).",
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $created++;
            }
        }

        $this->info("Created {$created} renewal reminder audit log(s).");
        return self::SUCCESS;
    }
}
