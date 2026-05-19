<?php

namespace App\Filament\Principal\Pages;

use App\Models\ProcurementRequest;
use App\Models\Student;
use Filament\Pages\Page;

class FinanceVisibility extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $title = 'Finance Visibility';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.principal.pages.finance-visibility';

    public function getViewData(): array
    {
        return [
            'arrears'      => Student::where('fee_status', 'arrears')->get(),
            'procurement'  => ProcurementRequest::whereIn('status', ['pending','principal_review','yayasan_review'])->get(),
            'totalAmount'  => ProcurementRequest::whereIn('status', ['pending','principal_review','yayasan_review'])->sum('amount'),
        ];
    }
}
