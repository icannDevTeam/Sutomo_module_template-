<?php

namespace App\Filament\Principal\Resources\LetterOfIntentResource\Pages;

use App\Filament\Principal\Resources\LetterOfIntentResource;
use App\Models\LetterOfIntent;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListLettersOfIntent extends ListRecords
{
    protected static string $resource = LetterOfIntentResource::class;

    public function getHeading(): string
    {
        return 'Letters of Intent — Overview';
    }

    public function getHeader(): ?View
    {
        return view('filament.principal.loi.list-header', [
            'heading'    => $this->getHeading(),
            'subheading' => 'Track continuation, declined & resigned-pending cases across academic years. The current academic year is pinned at the top; older years can be archived once closed.',
            'actions'    => $this->getCachedHeaderActions(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Send New LOI')
                ->icon('heroicon-o-paper-airplane'),
            Actions\Action::make('configure')
                ->label('Configuration')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->url(fn () => \App\Filament\Principal\Pages\LetterOfIntentConfig::getUrl()),
        ];
    }

    public function getTabs(): array
    {
        $continuation = fn (Builder $q) => $q->whereIn('status', ['draft', 'sent'])->whereNull('archived_at');
        $declined     = fn (Builder $q) => $q->where('status', 'declined')
            ->where(function (Builder $sub) {
                $sub->whereNull('follow_up_status')
                    ->orWhereNotIn('follow_up_status', ['closed']);
            })
            ->whereNull('archived_at');

        return [
            'continuation' => Tab::make('Continuation')
                ->icon('heroicon-o-arrow-path')
                ->modifyQueryUsing($continuation)
                ->badge(LetterOfIntent::query()->tap($continuation)->count())
                ->badgeColor('info'),

            'declined' => Tab::make('Declined / Resigned Pending')
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing($declined)
                ->badge(LetterOfIntent::query()->tap($declined)->count())
                ->badgeColor('warning'),

            'signed' => Tab::make('Signed')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'signed')->whereNull('archived_at')),

            'all' => Tab::make('All')
                ->icon('heroicon-o-rectangle-stack')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereNull('archived_at')),

            'archived' => Tab::make('Archived')
                ->icon('heroicon-o-archive-box')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereNotNull('archived_at')),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'continuation';
    }
}
