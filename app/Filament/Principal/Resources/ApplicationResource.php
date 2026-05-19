<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\ApplicationResource\Pages;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Applicant')->columns(2)->schema([
                Forms\Components\TextInput::make('code')->required(),
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Select::make('gender')->options(['M'=>'Male','F'=>'Female']),
                Forms\Components\DatePicker::make('dob'),
                Forms\Components\TextInput::make('current_school'),
                Forms\Components\Select::make('applicant_type')->options(['new'=>'New','transfer'=>'Transfer','sibling'=>'Sibling'])->default('new'),
            ]),
            Forms\Components\Section::make('Target Placement')->columns(2)->schema([
                Forms\Components\Select::make('campus')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International'])->required(),
                Forms\Components\TextInput::make('unit'),
                Forms\Components\TextInput::make('grade'),
                Forms\Components\Select::make('stream')->options(['ipa'=>'IPA','ips'=>'IPS','umum'=>'Umum']),
            ]),
            Forms\Components\Section::make('Pipeline')->columns(2)->schema([
                Forms\Components\Select::make('status')->options(Application::STATUSES)->default('submitted')->required(),
                Forms\Components\Select::make('payment_status')->options(['pending'=>'Pending','paid'=>'Paid','refunded'=>'Refunded'])->default('pending'),
                Forms\Components\DatePicker::make('applied_at')->default(now()),
                Forms\Components\DatePicker::make('exam_date'),
                Forms\Components\TextInput::make('placement_score')->numeric()->minValue(0)->maxValue(100),
                Forms\Components\TextInput::make('placement_recommendation'),
                Forms\Components\Toggle::make('waitlisted'),
            ]),
            Forms\Components\Section::make('Guardian')->columns(2)->schema([
                Forms\Components\TextInput::make('parent_name'),
                Forms\Components\TextInput::make('parent_phone')->tel(),
                Forms\Components\TextInput::make('parent_email')->email(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('campus')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('grade'),
                Tables\Columns\TextColumn::make('applicant_type')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => Application::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('placement_score')->sortable(),
                Tables\Columns\TextColumn::make('payment_status')->badge()
                    ->color(fn ($state) => $state === 'paid' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('applied_at')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International']),
                Tables\Filters\SelectFilter::make('status')->options(Application::STATUSES),
                Tables\Filters\SelectFilter::make('applicant_type')->options(['new'=>'New','transfer'=>'Transfer','sibling'=>'Sibling']),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit'   => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
