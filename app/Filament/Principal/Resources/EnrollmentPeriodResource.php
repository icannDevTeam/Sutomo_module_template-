<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\EnrollmentPeriodResource\Pages;
use App\Models\EnrollmentPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentPeriodResource extends Resource
{
    protected static ?string $model = EnrollmentPeriod::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 0;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $label = 'Enrollment Period';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Period')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Select::make('campus')->required()->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International']),
                Forms\Components\TextInput::make('unit'),
                Forms\Components\Select::make('status')->options(EnrollmentPeriod::STATUSES)->default('draft')->required(),
                Forms\Components\DatePicker::make('opens_at')->required(),
                Forms\Components\DatePicker::make('closes_at')->required(),
                Forms\Components\TextInput::make('quota')->numeric()->default(120),
            ]),
            Forms\Components\Section::make('Placement Exam')->columns(2)->schema([
                Forms\Components\TextInput::make('pass_threshold')->numeric()->default(70)->suffix('/ 100'),
                Forms\Components\TextInput::make('fail_threshold')->numeric()->default(50)->suffix('/ 100'),
                Forms\Components\DateTimePicker::make('exam_starts_at')->label('Exam Date & Time'),
                Forms\Components\TextInput::make('exam_venue'),
                Forms\Components\Textarea::make('exam_instructions')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('campus')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => EnrollmentPeriod::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('opens_at')->date(),
                Tables\Columns\TextColumn::make('closes_at')->date(),
                Tables\Columns\TextColumn::make('quota')->alignCenter(),
                Tables\Columns\TextColumn::make('applications_count')->counts('applications')->label('Apps')->alignCenter(),
                Tables\Columns\TextColumn::make('pass_threshold')->label('Pass ≥')->alignCenter(),
                Tables\Columns\TextColumn::make('exam_starts_at')->dateTime()->label('Exam'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')->label('Open Enrollment')->icon('heroicon-o-lock-open')->color('success')
                    ->visible(fn (EnrollmentPeriod $r) => $r->status !== 'open')
                    ->requiresConfirmation()
                    ->action(function (EnrollmentPeriod $r) {
                        $r->update(['status' => 'open']);
                        Notification::make()->title("'{$r->name}' is now OPEN for applications")->success()->send();
                    }),
                Tables\Actions\Action::make('close')->label('Close Enrollment')->icon('heroicon-o-lock-closed')->color('danger')
                    ->visible(fn (EnrollmentPeriod $r) => $r->status === 'open')
                    ->requiresConfirmation()
                    ->action(function (EnrollmentPeriod $r) {
                        $r->update(['status' => 'closed']);
                        Notification::make()->title("'{$r->name}' has been CLOSED")->warning()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnrollmentPeriods::route('/'),
            'create' => Pages\CreateEnrollmentPeriod::route('/create'),
            'edit'   => Pages\EditEnrollmentPeriod::route('/{record}/edit'),
        ];
    }
}
