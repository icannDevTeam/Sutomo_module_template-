<?php

namespace App\Filament\Principal\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Announcements extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $title = 'Announcements & PTA';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.principal.pages.announcements';

    public ?array $data = [];

    public function mount(): void { $this->form->fill(); }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Select::make('audience')->options([
                'all'     => 'All Parents',
                'sd'      => 'SD Parents',
                'smp'     => 'SMP Parents',
                'sma'     => 'SMA Parents',
                'staff'   => 'All Staff',
            ])->required(),
            Forms\Components\Select::make('channel')->options([
                'app'      => 'In-app',
                'email'    => 'Email',
                'whatsapp' => 'WhatsApp',
            ])->multiple()->required(),
            Forms\Components\Textarea::make('message')->rows(4)->required(),
        ])->statePath('data');
    }

    public function send(): void
    {
        $this->form->getState();
        Notification::make()
            ->title('Announcement queued')
            ->body('Will be delivered shortly.')
            ->success()->send();
        $this->form->fill();
    }
}
