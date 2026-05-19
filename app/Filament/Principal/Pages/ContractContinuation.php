<?php

namespace App\Filament\Principal\Pages;

use App\Models\Teacher;
use Filament\Pages\Page;

class ContractContinuation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationGroup = 'Teachers';
    protected static ?string $title = 'Contract Continuation & SK';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.principal.pages.contract-continuation';

    public function getViewData(): array
    {
        return [
            'expiring' => Teacher::whereNotNull('contract_end')
                ->whereDate('contract_end', '<=', now()->addMonths(3))
                ->orderBy('contract_end')->get(),
        ];
    }
}
