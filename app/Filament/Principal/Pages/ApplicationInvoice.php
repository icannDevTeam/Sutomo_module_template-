<?php

namespace App\Filament\Principal\Pages;

use App\Models\Application;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ApplicationInvoice extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.principal.pages.application-invoice';
    protected static ?string $slug = 'applications/{record}/invoice';

    public Application $record;

    public function mount(int|Application $record): void
    {
        $this->record = $record instanceof Application ? $record : Application::findOrFail($record);
        if (empty($this->record->invoice_no)) {
            $this->record->invoice_no = 'INV-' . now()->format('Y') . '-' . str_pad((string) $this->record->id, 5, '0', STR_PAD_LEFT);
            $this->record->save();
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Invoice ' . $this->record->invoice_no;
    }

    public static function getRoutePath(): string
    {
        return '/applications/{record}/invoice';
    }
}
