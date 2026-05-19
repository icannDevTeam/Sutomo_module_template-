<?php

namespace App\Filament\Principal\Pages;

use App\Models\Student;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class BukuIndukStudent extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Students';
    protected static ?string $title = 'Buku Induk — Master Student File';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.principal.pages.buku-induk';

    #[Url] public ?string $search = '';

    public function getViewData(): array
    {
        $query = Student::query();
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('nis', 'like', "%{$this->search}%");
            });
        }
        return ['students' => $query->orderBy('name')->take(50)->get()];
    }
}
