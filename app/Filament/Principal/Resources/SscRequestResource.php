<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\SscRequestResource\Pages;
use App\Models\SscRequest;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SscRequestResource extends Resource
{
    protected static ?string $model = SscRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'SSC Requests';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required(),
            Forms\Components\Select::make('student_id')->label('Student')
                ->options(fn () => Student::orderBy('name')->pluck('name','id'))
                ->searchable()->required(),
            Forms\Components\Select::make('type')->options(SscRequest::TYPES)->required(),
            Forms\Components\Select::make('priority')->options(['low'=>'Low','normal'=>'Normal','high'=>'High'])->default('normal'),
            Forms\Components\Select::make('status')->options(SscRequest::STATUSES)->default('pending')->required(),
            Forms\Components\DatePicker::make('requested_at')->default(now())->required(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('requested_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('student.name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('priority')->badge()
                    ->color(fn ($s) => $s === 'high' ? 'danger' : ($s === 'normal' ? 'gray' : 'info')),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => SscRequest::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('requested_at')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(SscRequest::STATUSES),
                Tables\Filters\SelectFilter::make('type')->options(SscRequest::TYPES),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSscRequests::route('/'),
            'create' => Pages\CreateSscRequest::route('/create'),
            'edit'   => Pages\EditSscRequest::route('/{record}/edit'),
        ];
    }
}
