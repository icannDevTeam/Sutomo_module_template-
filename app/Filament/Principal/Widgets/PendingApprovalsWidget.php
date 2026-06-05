<?php

namespace App\Filament\Principal\Widgets;

use App\Models\TeacherLeave;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class PendingApprovalsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $leaves = TeacherLeave::where('status', 'pending')->count();

        // Phase 2 queues — guarded so the widget renders before their migrations.
        $docs = Schema::hasTable('teacher_documents')
            ? \DB::table('teacher_documents')->where('status', 'pending')->count() : 0;

        $duties = Schema::hasTable('duty_assignments')
            ? \DB::table('duty_assignments')->where('status', 'pending')->count() : 0;

        $total = $leaves + $docs + $duties;

        return [
            Stat::make('Total Pending', (string) $total)
                ->description('Across every approval queue')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color($total > 0 ? 'warning' : 'success'),
            Stat::make('Leave Requests', (string) $leaves)
                ->url(route('filament.principal.resources.teacher-leaves.index'))
                ->color($leaves > 0 ? 'warning' : 'gray'),
            Stat::make('Document Verifications', (string) $docs)
                ->color($docs > 0 ? 'warning' : 'gray'),
            Stat::make('Duty Assignments', (string) $duties)
                ->description('Pending substitution duty handoffs')
                ->color($duties > 0 ? 'warning' : 'gray'),
        ];
    }
}
