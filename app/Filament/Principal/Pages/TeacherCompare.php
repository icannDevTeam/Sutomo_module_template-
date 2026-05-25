<?php

namespace App\Filament\Principal\Pages;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;

class TeacherCompare extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Teachers';
    protected static ?string $title = 'Compare Teachers';
    protected static ?int $navigationSort = 7;
    protected static string $view = 'filament.principal.pages.teacher-compare';

    public ?array $data = ['ids' => []];

    public function mount(): void
    {
        $ids = request()->query('ids');
        if (is_string($ids)) {
            $this->data['ids'] = collect(explode(',', $ids))->filter()->map('intval')->take(3)->all();
        }
        $this->form->fill(['ids' => $this->data['ids']]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('ids')
                    ->label('Pick up to 3 teachers')
                    ->multiple()
                    ->maxItems(3)
                    ->options(Teacher::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->live(),
            ])
            ->statePath('data');
    }

    public function getViewData(): array
    {
        $ids = $this->data['ids'] ?? [];
        $teachers = Teacher::with(['homeroomClasses', 'mentor'])->whereIn('id', $ids)->get();

        $cards = $teachers->map(function ($t) {
            $start = now()->startOfMonth();
            $rows = TeacherAttendance::where('teacher_id', $t->id)->where('date', '>=', $start)->get();
            $p = $rows->where('status', 'present')->count();
            $base = $p + $rows->where('status', 'late')->count() + $rows->where('status', 'absent')->count();
            return [
                'teacher'       => $t,
                'attendance'    => $base ? round(($p / $base) * 100) : null,
                'cert_count'    => $t->formalCertifications()->count(),
                'obs_count'     => $t->observations()->count(),
                'homeroom_count'=> $t->homeroomClasses()->count(),
            ];
        });

        return ['cards' => $cards];
    }
}
