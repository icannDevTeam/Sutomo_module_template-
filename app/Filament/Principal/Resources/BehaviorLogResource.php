<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\BehaviorLogResource\Pages;
use App\Models\BehaviorLog;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BehaviorLogResource extends Resource
{
    protected static ?string $model = BehaviorLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Behavior Logs';
    protected static ?string $navigationGroup = 'Students';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')->label('Student')
                ->options(fn () => Student::orderBy('name')->pluck('name','id'))
                ->searchable()->required(),
            Forms\Components\Select::make('teacher_id')->label('Reporting Teacher')
                ->options(fn () => Teacher::orderBy('name')->pluck('name','id'))->searchable(),
            Forms\Components\DatePicker::make('occurred_at')->default(now())->required(),
            Forms\Components\Select::make('category')->options(BehaviorLog::CATEGORIES)->required(),
            Forms\Components\Select::make('severity')->options(BehaviorLog::SEVERITIES)->default('low')->required(),
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Textarea::make('notes')->rows(3),
            Forms\Components\Select::make('status')->options(BehaviorLog::STATUSES)->default('open')->required(),
            Forms\Components\TextInput::make('action_taken'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('student.name')->searchable(),
                Tables\Columns\TextColumn::make('teacher.name')->label('Teacher')->toggleable(),
                Tables\Columns\TextColumn::make('title')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('severity')->badge()
                    ->color(fn ($state) => BehaviorLog::SEVERITY_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')->options(BehaviorLog::SEVERITIES),
                Tables\Filters\SelectFilter::make('status')->options(BehaviorLog::STATUSES),
                Tables\Filters\SelectFilter::make('category')->options(BehaviorLog::CATEGORIES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('escalate')
                    ->icon('heroicon-o-arrow-trending-up')->color('warning')
                    ->visible(fn ($record) => ! in_array($record->status, ['closed','principal_action']))
                    ->action(fn ($record) => $record->update([
                        'status' => match ($record->status) {
                            'open' => 'unit_review', 'unit_review' => 'vp_review',
                            'vp_review' => 'principal_action', default => 'closed',
                        },
                    ])),
                Tables\Actions\Action::make('close')
                    ->icon('heroicon-o-check')->color('success')
                    ->visible(fn ($record) => $record->status !== 'closed')
                    ->action(fn ($record) => $record->update(['status' => 'closed'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBehaviorLogs::route('/'),
            'create' => Pages\CreateBehaviorLog::route('/create'),
            'edit'   => Pages\EditBehaviorLog::route('/{record}/edit'),
        ];
    }
}
