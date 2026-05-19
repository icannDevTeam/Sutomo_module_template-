<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\ApplicationResource\Pages;
use App\Models\Application;
use App\Models\EnrollmentPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

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
            Forms\Components\Wizard::make([
                Forms\Components\Wizard\Step::make('A. Data Siswa')->icon('heroicon-o-user')->columns(2)->schema([
                    Forms\Components\TextInput::make('code')->label('Application Code')->placeholder('Auto on save')->maxLength(20),
                    Forms\Components\TextInput::make('nisn')->label('NISN (Nomor Induk Siswa Nasional)')->maxLength(20),
                    Forms\Components\TextInput::make('name')->label('Nama Lengkap (sesuai Akta Lahir)')->required(),
                    Forms\Components\Select::make('gender')->label('Jenis Kelamin')->options(['M'=>'Laki-laki','F'=>'Perempuan'])->required(),
                    Forms\Components\TextInput::make('birthplace')->label('Tempat Lahir'),
                    Forms\Components\DatePicker::make('dob')->label('Tanggal Lahir')->required(),
                    Forms\Components\Select::make('religion')->label('Agama')->options(['Islam'=>'Islam','Kristen'=>'Kristen','Katolik'=>'Katolik','Buddha'=>'Buddha','Hindu'=>'Hindu','Khonghucu'=>'Khonghucu']),
                    Forms\Components\TextInput::make('ethnicity')->label('Suku'),
                    Forms\Components\TextInput::make('address')->label('Alamat')->columnSpanFull(),
                    Forms\Components\TextInput::make('city')->label('Kabupaten / Kota'),
                    Forms\Components\TextInput::make('current_school')->label('Nama Sekolah Asal'),
                    Forms\Components\Select::make('applicant_type')->label('Applicant Type')->options(Application::APPLICANT_TYPES)->default('new')->required()->live(),
                    Forms\Components\Select::make('orphan_status')->label('Anak Yatim/Piatu')->options(Application::ORPHAN_STATUSES)->default('none'),
                    Forms\Components\Toggle::make('is_teacher_child')->label('Child of a Sutomo teacher')->inline(false),
                    Forms\Components\Toggle::make('is_existing_student')->label('Already a Sutomo student (sibling/transfer-in)')->inline(false)->live(),
                    Forms\Components\Select::make('existing_unit')->label('Current Sutomo Unit')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International'])->visible(fn (Forms\Get $get)=>$get('is_existing_student')),
                ]),
                Forms\Components\Wizard\Step::make('B. Target Placement')->icon('heroicon-o-academic-cap')->columns(2)->schema([
                    Forms\Components\Select::make('enrollment_period_id')->label('Enrollment Period')
                        ->options(EnrollmentPeriod::query()->orderByDesc('opens_at')->pluck('name','id'))
                        ->searchable(),
                    Forms\Components\Select::make('campus')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International'])->required()->live(),
                    Forms\Components\TextInput::make('unit')->label('Unit')->placeholder('e.g. SMA Sutomo 1'),
                    Forms\Components\TextInput::make('grade')->label('Kelas (Grade)')->required(),
                    Forms\Components\Select::make('stream')->label('Jurusan (Stream)')->options(['ipa'=>'IPA','ips'=>'IPS','umum'=>'Umum'])
                        ->visible(fn (Forms\Get $get) => $get('campus') === 'sma'),
                ]),
                Forms\Components\Wizard\Step::make('C. Wali / Guardian')->icon('heroicon-o-users')->columns(2)->schema([
                    Forms\Components\TextInput::make('parent_name')->label('Nama Orang Tua / Wali')->required(),
                    Forms\Components\TextInput::make('parent_phone')->label('Nomor Handphone')->tel()->required(),
                    Forms\Components\TextInput::make('parent_whatsapp')->label('Nomor WhatsApp')->tel()
                        ->helperText('Leave blank if same as phone'),
                    Forms\Components\TextInput::make('parent_email')->label('Email')->email()->required(),
                    Forms\Components\TextInput::make('parent_occupation')->label('Pekerjaan'),
                ]),
                Forms\Components\Wizard\Step::make('D. Pembayaran')->icon('heroicon-o-banknotes')->columns(2)->schema([
                    Forms\Components\Placeholder::make('fee')->label('Application Fee')
                        ->content('Rp 300.000 — non-refundable'),
                    Forms\Components\Select::make('payment_method')->label('Payment Method')->options(Application::PAYMENT_METHODS),
                    Forms\Components\TextInput::make('payment_amount')->label('Amount Paid (Rp)')->numeric()->default(300000),
                    Forms\Components\DateTimePicker::make('payment_paid_at')->label('Paid At'),
                    Forms\Components\TextInput::make('invoice_no')->label('Invoice No')->placeholder('Auto on save'),
                    Forms\Components\Select::make('payment_status')->options(['pending'=>'Pending','paid'=>'Paid','refunded'=>'Refunded'])->default('pending'),
                    Forms\Components\FileUpload::make('receipt_file')->label('Upload Receipt / Bukti Transfer')
                        ->disk('public')->directory('receipts')
                        ->acceptedFileTypes(['image/*','application/pdf'])
                        ->maxSize(4096)->columnSpanFull(),
                ]),
                Forms\Components\Wizard\Step::make('E. Pipeline & Decision')->icon('heroicon-o-flag')->columns(2)->schema([
                    Forms\Components\Select::make('status')->options(Application::STATUSES)->default('submitted')->required(),
                    Forms\Components\DatePicker::make('applied_at')->default(now()),
                    Forms\Components\DateTimePicker::make('exam_date'),
                    Forms\Components\TextInput::make('placement_score')->numeric()->minValue(0)->maxValue(100)
                        ->helperText('Saving a score auto-promotes to Passed / Failed based on Enrollment Period thresholds.'),
                    Forms\Components\TextInput::make('placement_recommendation'),
                    Forms\Components\Toggle::make('waitlisted'),
                    Forms\Components\TextInput::make('decline_reason')->columnSpanFull(),
                ]),
            ])->columnSpanFull()->skippable(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('A. Data Siswa')->columns(3)->schema([
                Infolists\Components\TextEntry::make('code')->badge()->color('primary'),
                Infolists\Components\TextEntry::make('name')->weight('bold')->size('lg'),
                Infolists\Components\TextEntry::make('nisn')->label('NISN'),
                Infolists\Components\TextEntry::make('gender')->formatStateUsing(fn ($state)=>$state==='M'?'Laki-laki':'Perempuan'),
                Infolists\Components\TextEntry::make('dob')->date(),
                Infolists\Components\TextEntry::make('birthplace'),
                Infolists\Components\TextEntry::make('religion'),
                Infolists\Components\TextEntry::make('ethnicity'),
                Infolists\Components\TextEntry::make('city'),
                Infolists\Components\TextEntry::make('address')->columnSpanFull(),
                Infolists\Components\TextEntry::make('current_school'),
                Infolists\Components\TextEntry::make('applicant_type')->badge(),
                Infolists\Components\TextEntry::make('orphan_status')->badge()->color('gray'),
                Infolists\Components\IconEntry::make('is_teacher_child')->boolean()->label('Teacher child'),
                Infolists\Components\IconEntry::make('is_existing_student')->boolean()->label('Existing Sutomo student'),
                Infolists\Components\TextEntry::make('existing_unit')->visible(fn ($record)=>$record->is_existing_student),
            ]),
            Infolists\Components\Section::make('B. Target Placement')->columns(3)->schema([
                Infolists\Components\TextEntry::make('enrollmentPeriod.name')->label('Enrollment Period')->badge()->color('info'),
                Infolists\Components\TextEntry::make('campus')->badge(),
                Infolists\Components\TextEntry::make('unit'),
                Infolists\Components\TextEntry::make('grade'),
                Infolists\Components\TextEntry::make('stream'),
            ]),
            Infolists\Components\Section::make('C. Wali / Guardian')->columns(3)->schema([
                Infolists\Components\TextEntry::make('parent_name'),
                Infolists\Components\TextEntry::make('parent_phone'),
                Infolists\Components\TextEntry::make('parent_whatsapp')->label('WhatsApp'),
                Infolists\Components\TextEntry::make('parent_email')->copyable(),
                Infolists\Components\TextEntry::make('parent_occupation'),
            ]),
            Infolists\Components\Section::make('D. Payment')->columns(3)->schema([
                Infolists\Components\TextEntry::make('invoice_no')->label('Invoice No')->badge()->color('primary'),
                Infolists\Components\TextEntry::make('payment_method')->badge(),
                Infolists\Components\TextEntry::make('payment_amount')->money('IDR'),
                Infolists\Components\TextEntry::make('payment_paid_at')->dateTime(),
                Infolists\Components\TextEntry::make('payment_status')->badge()
                    ->color(fn ($state)=>$state==='paid'?'success':'warning'),
                Infolists\Components\TextEntry::make('receipt_file')->label('Receipt')
                    ->formatStateUsing(fn ($state)=>$state?'View uploaded file':'Not uploaded')
                    ->url(fn ($state)=>$state?Storage::disk('public')->url($state):null, true)
                    ->color(fn ($state)=>$state?'success':'gray'),
            ]),
            Infolists\Components\Section::make('E. Pipeline & Decision')->columns(3)->schema([
                Infolists\Components\TextEntry::make('status')->badge()
                    ->color(fn ($state)=>Application::STATUS_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn ($state)=>Application::STATUSES[$state] ?? $state),
                Infolists\Components\TextEntry::make('applied_at')->date(),
                Infolists\Components\TextEntry::make('exam_date')->dateTime(),
                Infolists\Components\TextEntry::make('placement_score'),
                Infolists\Components\TextEntry::make('placement_recommendation'),
                Infolists\Components\IconEntry::make('waitlisted')->boolean(),
                Infolists\Components\TextEntry::make('decline_reason')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold')
                    ->description(fn ($record) => $record->is_teacher_child ? '★ Teacher child' : null),
                Tables\Columns\TextColumn::make('campus')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('grade'),
                Tables\Columns\TextColumn::make('applicant_type')->badge()
                    ->color(fn ($state) => match($state) { 'transfer'=>'info','sibling'=>'warning','returning'=>'primary',default=>'gray' }),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => Application::STATUS_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn ($state) => Application::STATUSES[$state] ?? $state),
                Tables\Columns\TextColumn::make('placement_score')->sortable()->alignCenter()
                    ->color(fn ($state) => $state === null ? 'gray' : ($state >= 70 ? 'success' : ($state < 50 ? 'danger' : 'warning'))),
                Tables\Columns\TextColumn::make('payment_method')->badge()->color('gray')->toggleable(),
                Tables\Columns\TextColumn::make('payment_status')->badge()
                    ->color(fn ($state) => $state === 'paid' ? 'success' : 'warning'),
                Tables\Columns\IconColumn::make('receipt_file')->label('Receipt')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('applied_at')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('enrollment_period_id')->label('Period')
                    ->options(EnrollmentPeriod::query()->orderByDesc('opens_at')->pluck('name','id')),
                Tables\Filters\SelectFilter::make('campus')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International']),
                Tables\Filters\SelectFilter::make('status')->options(Application::STATUSES),
                Tables\Filters\SelectFilter::make('applicant_type')->options(Application::APPLICANT_TYPES),
                Tables\Filters\TernaryFilter::make('is_teacher_child')->label('Teacher child'),
                Tables\Filters\TernaryFilter::make('is_existing_student')->label('Existing student'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('invoice')->label('Open Invoice')->icon('heroicon-o-document-currency-dollar')->color('primary')
                        ->url(fn (Application $r) => route('filament.principal.pages.application-invoice', ['record' => $r->id]))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('receipt')->label('View Receipt')->icon('heroicon-o-paper-clip')->color('warning')
                        ->visible(fn (Application $r) => filled($r->receipt_file))
                        ->url(fn (Application $r) => Storage::disk('public')->url($r->receipt_file))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('mark_passed')->label('Mark Passed')->icon('heroicon-o-check-circle')->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Application $r) => self::transition($r, 'passed', 'Marked as Passed')),
                    Tables\Actions\Action::make('mark_failed')->label('Mark Failed')->icon('heroicon-o-x-circle')->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Application $r) => self::transition($r, 'failed', 'Marked as Failed')),
                    Tables\Actions\Action::make('waitlist')->label('Move to Waitlist')->icon('heroicon-o-clock')->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Application $r) => self::transition($r, 'waitlisted', 'Moved to Waitlist', ['waitlisted' => true])),
                    Tables\Actions\Action::make('accept')->label('Accept')->icon('heroicon-o-hand-thumb-up')->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Application $r) => self::transition($r, 'accepted', 'Application accepted')),
                    Tables\Actions\Action::make('decline')->label('Decline')->icon('heroicon-o-hand-thumb-down')->color('danger')
                        ->form([Forms\Components\Textarea::make('decline_reason')->required()])
                        ->action(function (Application $r, array $data) { self::transition($r, 'declined', 'Application declined', $data); }),
                    Tables\Actions\Action::make('schedule_exam')->label('Schedule Exam')->icon('heroicon-o-calendar-days')->color('info')
                        ->form([Forms\Components\DateTimePicker::make('exam_date')->required()])
                        ->action(function (Application $r, array $data) { self::transition($r, 'exam_scheduled', 'Exam scheduled', $data); }),
                    Tables\Actions\Action::make('enter_score')->label('Enter Score')->icon('heroicon-o-pencil-square')->color('primary')
                        ->form([Forms\Components\TextInput::make('placement_score')->numeric()->minValue(0)->maxValue(100)->required()])
                        ->action(function (Application $r, array $data) {
                            $newStatus = $r->applyExamScore((float) $data['placement_score']);
                            Notification::make()->title('Score saved')->body("Status: {$newStatus}")->success()->send();
                        }),
                ])->label('Decision')->icon('heroicon-m-ellipsis-vertical')->button()->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('apply_threshold')->label('Apply Exam Threshold')->icon('heroicon-o-calculator')->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $changed = 0;
                            foreach ($records as $r) {
                                if ($r->placement_score !== null) { $r->applyExamScore((float)$r->placement_score); $changed++; }
                            }
                            Notification::make()->title("Updated {$changed} applicants from thresholds")->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('bulk_waitlist')->label('Bulk: Waitlist')->icon('heroicon-o-clock')->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $r) { $r->update(['status'=>'waitlisted','waitlisted'=>true]); }
                            Notification::make()->title('Selected applicants moved to waitlist')->warning()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('applied_at', 'desc');
    }

    public static function transition(Application $r, string $status, string $msg, array $extra = []): void
    {
        $r->fill(array_merge(['status' => $status], $extra));
        if ($status === 'accepted' && empty($r->invoice_no)) {
            $r->invoice_no = 'INV-' . now()->format('Y') . '-' . str_pad((string)$r->id, 5, '0', STR_PAD_LEFT);
        }
        $r->save();
        Notification::make()->title($msg)->success()->send();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view'   => Pages\ViewApplication::route('/{record}'),
            'edit'   => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
