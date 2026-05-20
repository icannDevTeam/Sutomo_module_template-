<?php

namespace App\Filament\Principal\Widgets;

use App\Models\ProcurementRequest;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceVisibility extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    /** Render the 3 stats in a single horizontal row (Filament default is 1col @ < md). */
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $arrears      = Student::where('fee_status', 'arrears')->count();
        $proc         = ProcurementRequest::whereIn('status', ['pending', 'principal_review'])->count();
        $totalProcAmt = ProcurementRequest::whereIn('status', ['pending', 'principal_review'])->sum('amount');

        return [
            Stat::make('Fee Arrears', (string) $arrears)
                ->description($arrears > 0 ? 'students with unpaid fees' : 'all students current')
                ->descriptionIcon($arrears > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($arrears > 10 ? 'danger' : ($arrears > 0 ? 'warning' : 'success')),

            Stat::make('Procurement Pending', (string) $proc)
                ->description($proc > 0 ? 'awaiting principal review' : 'queue clear')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color($proc > 0 ? 'info' : 'gray'),

            Stat::make('Pending Amount', 'Rp ' . number_format((float) $totalProcAmt, 0, ',', '.'))
                ->description('total value of open requests')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}
