<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\Teacher;
use App\Support\CsvExporter;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class BukuInduk extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'People';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.buku-induk';
    protected static ?string $title = 'Buku Induk Guru';
    protected static ?string $navigationLabel = 'Buku Induk';

    #[Url(as: 'q')] public string $q = '';
    #[Url] public string $status = 'all';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => CsvExporter::download(
                    $this->buildQuery()->get(),
                    TeacherResource::csvColumns(),
                    CsvExporter::filename('buku-induk'),
                )),
        ];
    }

    protected function buildQuery()
    {
        $q = Teacher::query();
        if ($this->q !== '') {
            $term = '%' . strtolower($this->q) . '%';
            $q->whereRaw('lower(name) like ?', [$term])
              ->orWhereRaw('lower(code) like ?', [$term])
              ->orWhereRaw('lower(subject) like ?', [$term]);
        }
        if ($this->status !== 'all') $q->where('status', $this->status);
        return $q;
    }

    public function getViewData(): array
    {
        $q = $this->buildQuery();
        return ['teachers' => $q->orderBy('name')->get()];
    }
}
