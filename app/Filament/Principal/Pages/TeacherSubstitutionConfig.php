<?php

namespace App\Filament\Principal\Pages;

use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Auth;

class TeacherSubstitutionConfig extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Leave & Substitution';
    protected static ?string $navigationLabel = 'Substitution Config';
    protected static ?string $title = 'Teacher Substitution Configuration';
    protected static ?int $navigationSort = 90;
    protected static string $view = 'filament.principal.pages.teacher-substitution-config';
    protected static ?string $slug = 'substitution-config';

    public function getSubtitle(): ?string
    {
        return 'Bias the auto-search engine: mark teachers as VIP (always invited first), Restricted (invited only as a last resort), or Blocked (never invited).';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Teacher::query()
                    ->where('status', '!=', 'alumni')
                    ->orderBy('name')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Teacher $r) => trim(($r->subject ?? '—') . ' · ' . ($r->dept ?? '—'))),
                Tables\Columns\TextColumn::make('campus')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Teacher::STATUSES[$state] ?? $state)
                    ->toggleable(),
                Tables\Columns\SelectColumn::make('substitution_category')
                    ->label('Substitution Category')
                    ->options(Teacher::SUBSTITUTION_CATEGORIES)
                    ->selectablePlaceholder(false)
                    ->beforeStateUpdated(function (Teacher $record, $state) {
                        $record->forceFill([
                            'substitution_category_set_by' => Auth::id(),
                            'substitution_category_set_at' => now(),
                        ])->save();
                    })
                    ->afterStateUpdated(function (Teacher $record, $state) {
                        Notification::make()
                            ->title("Set {$record->name} → " . (Teacher::SUBSTITUTION_CATEGORIES[$state] ?? $state))
                            ->success()
                            ->send();
                    }),
                Tables\Columns\TextColumn::make('substitution_category_set_at')
                    ->label('Set at')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('substitutionCategorySetter.name')
                    ->label('Set by')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('substitution_category_note')
                    ->label('Note')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('substitution_category')
                    ->label('Category')
                    ->options(Teacher::SUBSTITUTION_CATEGORIES),
                Tables\Filters\SelectFilter::make('campus')
                    ->options(fn () => Teacher::query()
                        ->whereNotNull('campus')
                        ->distinct()
                        ->pluck('campus', 'campus')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('dept')
                    ->label('Department')
                    ->options(fn () => Teacher::query()
                        ->whereNotNull('dept')
                        ->distinct()
                        ->pluck('dept', 'dept')
                        ->toArray()),
                Tables\Filters\Filter::make('uncategorized')
                    ->label('Standard / Uncategorized only')
                    ->query(fn (Builder $q) => $q->where(function ($q2) {
                        $q2->where('substitution_category', 'standard')
                            ->orWhereNull('substitution_category');
                    })),
            ])
            ->actions([
                Tables\Actions\Action::make('setCategory')
                    ->label('Set with note')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->fillForm(fn (Teacher $r) => [
                        'substitution_category'      => $r->substitution_category ?? 'standard',
                        'substitution_category_note' => $r->substitution_category_note,
                    ])
                    ->form([
                        Forms\Components\Select::make('substitution_category')
                            ->label('Category')
                            ->options(Teacher::SUBSTITUTION_CATEGORIES)
                            ->required()
                            ->helperText(fn ($state) => Teacher::SUBSTITUTION_CATEGORY_DESCRIPTIONS[$state] ?? null)
                            ->live(),
                        Forms\Components\Textarea::make('substitution_category_note')
                            ->label('Note (optional, e.g. "On medical restriction until July")')
                            ->rows(2),
                    ])
                    ->action(function (array $data, Teacher $record) {
                        $record->forceFill([
                            'substitution_category'         => $data['substitution_category'],
                            'substitution_category_note'    => $data['substitution_category_note'] ?? null,
                            'substitution_category_set_by'  => Auth::id(),
                            'substitution_category_set_at'  => now(),
                        ])->save();
                        Notification::make()->title('Category updated')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulkSetCategory')
                    ->label('Set Category')
                    ->icon('heroicon-o-tag')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('substitution_category')
                            ->label('Category')
                            ->options(Teacher::SUBSTITUTION_CATEGORIES)
                            ->required()
                            ->helperText(fn ($state) => Teacher::SUBSTITUTION_CATEGORY_DESCRIPTIONS[$state] ?? null)
                            ->live(),
                        Forms\Components\Textarea::make('substitution_category_note')
                            ->label('Note (applied to all selected)')
                            ->rows(2),
                    ])
                    ->action(function (array $data, EloquentCollection $records) {
                        $now = now();
                        $uid = Auth::id();
                        foreach ($records as $r) {
                            $r->forceFill([
                                'substitution_category'         => $data['substitution_category'],
                                'substitution_category_note'    => $data['substitution_category_note'] ?? null,
                                'substitution_category_set_by'  => $uid,
                                'substitution_category_set_at'  => $now,
                            ])->save();
                        }
                        Notification::make()
                            ->title('Updated ' . $records->count() . ' teachers → ' . (Teacher::SUBSTITUTION_CATEGORIES[$data['substitution_category']] ?? ''))
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('bulkClear')
                    ->label('Reset to Standard')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (EloquentCollection $records) {
                        foreach ($records as $r) {
                            $r->forceFill([
                                'substitution_category'         => 'standard',
                                'substitution_category_note'    => null,
                                'substitution_category_set_by'  => Auth::id(),
                                'substitution_category_set_at'  => now(),
                            ])->save();
                        }
                        Notification::make()->title('Reset ' . $records->count() . ' teachers to Standard')->success()->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultPaginationPageOption(25)
            ->striped();
    }

    public function getViewData(): array
    {
        $counts = Teacher::query()
            ->where('status', '!=', 'alumni')
            ->selectRaw('substitution_category, COUNT(*) as c')
            ->groupBy('substitution_category')
            ->pluck('c', 'substitution_category')
            ->toArray();

        $summary = [];
        foreach (Teacher::SUBSTITUTION_CATEGORIES as $key => $label) {
            $summary[] = [
                'key'         => $key,
                'label'       => $label,
                'count'       => $counts[$key] ?? 0,
                'color'       => Teacher::SUBSTITUTION_CATEGORY_COLORS[$key] ?? 'gray',
                'description' => Teacher::SUBSTITUTION_CATEGORY_DESCRIPTIONS[$key] ?? '',
            ];
        }

        return ['summary' => $summary];
    }
}
