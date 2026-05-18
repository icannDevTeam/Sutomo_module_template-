<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Admin';
    protected static ?int $navigationSort = 9;
    protected static string $view = 'filament.pages.settings';
    protected static ?string $title = 'Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Cache::get('sutomo.settings', [
            'org_name'         => 'Yayasan Sutomo',
            'school_year'      => '2026/2027',
            'deposit_amount'   => 5000000,
            'deposit_due_days' => 7,
            'pass_threshold'   => 70,
            'opl_weeks'        => 8,
            'probation_months' => 6,
        ]));
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Organization')->columns(2)->schema([
                Forms\Components\TextInput::make('org_name')->label('Organization name')->required(),
                Forms\Components\TextInput::make('school_year')->label('Academic year')->required(),
            ]),
            Forms\Components\Section::make('Hiring policy')->columns(2)->schema([
                Forms\Components\TextInput::make('deposit_amount')->numeric()->prefix('Rp')->label('Deposit amount'),
                Forms\Components\TextInput::make('deposit_due_days')->numeric()->suffix('days')->label('Deposit due in'),
                Forms\Components\TextInput::make('pass_threshold')->numeric()->suffix('/100')->label('Written test pass threshold'),
            ]),
            Forms\Components\Section::make('Onboarding')->columns(2)->schema([
                Forms\Components\TextInput::make('opl_weeks')->numeric()->suffix('weeks')->label('OPL duration'),
                Forms\Components\TextInput::make('probation_months')->numeric()->suffix('months')->label('Probation duration'),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        Cache::forever('sutomo.settings', $this->form->getState());
        Notification::make()->title('Settings saved')->success()->send();
    }
}
