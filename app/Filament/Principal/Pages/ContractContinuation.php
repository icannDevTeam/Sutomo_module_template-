<?php

namespace App\Filament\Principal\Pages;

use Filament\Pages\Page;

/** Compatibility route for bookmarks; the contract workflow now lives in one hub. */
class ContractContinuation extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'contract-continuation';
    protected static ?string $title = 'Contract Workflow';
    protected static string $view = 'filament.principal.pages.contract-workflow-redirect';

    public function mount(): void
    {
        $this->redirect(MySubmissions::getUrl(
            ['type' => 'annual_continuation'],
            panel: 'principal'
        ));
    }
}
