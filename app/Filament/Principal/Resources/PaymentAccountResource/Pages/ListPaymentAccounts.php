<?php
namespace App\Filament\Principal\Resources\PaymentAccountResource\Pages;
use App\Filament\Principal\Resources\PaymentAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListPaymentAccounts extends ListRecords {
    protected static string $resource = PaymentAccountResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
