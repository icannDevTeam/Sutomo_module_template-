<?php

namespace App\Filament\Principal\Widgets;

use App\Models\ProcurementRequest;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceVisibility extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 1;

    protected function getStats(): array
    {
        $arrears = Student::where('fee_status', 'arrears')->count();
        $proc    = ProcurementRequest::whereIn('status', ['pending','principal_review'])->count();
        $totalProcAmt = ProcurementRequest::whereIn('status', ['pending','principal_review'])->sum('amount');

        return [
            Stat::make('Fee Arrears', (string) $arrears)->color($arrears > 10 ? 'danger' : 'warning'),
            Stat::make('Procurement Open', (string) $proc)->color('info'),
            Stat::make('Pending Amount', 'Rp '.number_format($totalProcAmt))->color('primary'),
        ];
    }
}
