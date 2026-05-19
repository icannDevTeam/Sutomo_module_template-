<?php

namespace App\Filament\Principal\Pages;

use App\Models\Student;
use Filament\Pages\Page;

class ParentAccounts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Students';
    protected static ?string $title = 'Parent Directory';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.principal.pages.parent-accounts';

    public function getViewData(): array
    {
        return [
            'students' => Student::whereNotNull('parent_email')->orderBy('name')->take(80)->get(),
        ];
    }
}
