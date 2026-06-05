<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['key' => 'sick',           'label' => 'Sick Leave',                    'affects_quota' => true,  'requires_substitute' => true,  'max_days' => null, 'sort_order' => 10, 'color' => 'danger',  'icon' => 'heroicon-o-heart',             'description' => 'Illness, medical appointments — counts against the leave quota.'],
            ['key' => 'emergency',      'label' => 'Emergency',                     'affects_quota' => true,  'requires_substitute' => true,  'max_days' => null, 'sort_order' => 20, 'color' => 'warning', 'icon' => 'heroicon-o-bolt',              'description' => 'Family emergency or unplanned personal matter.'],
            ['key' => 'sabbatical',     'label' => 'Sabbatical',                    'affects_quota' => true,  'requires_substitute' => true,  'max_days' => null, 'sort_order' => 30, 'color' => 'info',    'icon' => 'heroicon-o-academic-cap',      'description' => 'Approved long leave for study, research, or rest.'],
            ['key' => 'midday',         'label' => 'Mid-day Absence',               'affects_quota' => true,  'requires_substitute' => true,  'max_days' => null, 'sort_order' => 40, 'color' => 'gray',    'icon' => 'heroicon-o-clock',             'description' => 'Half-day absence — typically counts as part of quota.'],
            ['key' => 'prior',          'label' => 'Prior Notice',                  'affects_quota' => true,  'requires_substitute' => true,  'max_days' => null, 'sort_order' => 50, 'color' => 'primary', 'icon' => 'heroicon-o-calendar',          'description' => 'Planned leave booked ahead of time.'],
            ['key' => 'brief_absence',  'label' => 'Brief Absence',                 'affects_quota' => false, 'requires_substitute' => false, 'max_days' => null, 'sort_order' => 60, 'color' => 'gray',    'icon' => 'heroicon-o-arrow-uturn-right', 'description' => 'Short absence (e.g. doctor run during a free period). Does NOT count against quota and does NOT require a substitute.'],
            ['key' => 'assigned_work',  'label' => 'Assigned Duty (Out of School)', 'affects_quota' => false, 'requires_substitute' => false, 'max_days' => null, 'sort_order' => 70, 'color' => 'success', 'icon' => 'heroicon-o-briefcase',         'description' => 'Assigned duty out of school. This is an official school assignment and does not deduct leave quota.'],
            ['key' => 'bereavement',    'label' => 'Bereavement',                   'affects_quota' => false, 'requires_substitute' => true,  'max_days' => 4,    'sort_order' => 80, 'color' => 'gray',    'icon' => 'heroicon-o-hand-raised',       'description' => 'Family bereavement leave, capped at 4 days.'],
            ['key' => 'marriage',       'label' => 'Marriage',                      'affects_quota' => false, 'requires_substitute' => true,  'max_days' => 7,    'sort_order' => 90, 'color' => 'primary', 'icon' => 'heroicon-o-heart',             'description' => 'Marriage leave, capped at 7 days.'],
        ];

        foreach ($rows as $row) {
            LeaveType::updateOrCreate(
                ['key' => $row['key']],
                $row + ['is_active' => true],
            );
        }
    }
}
