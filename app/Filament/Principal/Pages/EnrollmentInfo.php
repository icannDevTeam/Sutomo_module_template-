<?php

namespace App\Filament\Principal\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class EnrollmentInfo extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 0;
    protected static ?string $title = 'Enrollment Information';
    protected static ?string $navigationLabel = 'Enrollment Info';
    protected static ?string $slug = 'enrollment-info';
    protected static string $view = 'filament.principal.pages.enrollment-info';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    protected const STORAGE_PATH = 'public/settings/enrollment-info.json';

    public function mount(): void
    {
        $this->form->fill(self::load());
    }

    public static function load(): array
    {
        if (Storage::exists(self::STORAGE_PATH)) {
            $raw = json_decode(Storage::get(self::STORAGE_PATH), true);
            if (is_array($raw)) return $raw;
        }
        return self::defaults();
    }

    public static function defaults(): array
    {
        return [
            'headline'      => 'Penerimaan Peserta Didik Baru — SMP Sutomo 2',
            'intro'         => 'Selamat datang di portal pendaftaran SMP Sutomo 2. Mohon baca syarat pendaftaran dan panduan di bawah ini sebelum mengisi formulir.',
            'requirements'  => "Calon peserta didik yang mendaftar ke SMP Sutomo 2 harus mematuhi:\n• Peraturan dan tata tertib sekolah.\n• Tidak merokok, minum minuman keras, dan terlibat dalam narkoba.",
            'documents'     => "1. Surat Keterangan Lulus SD\n2. Fotokopi Rapor SD\n3. Fotokopi Akta Kelahiran\n4. Fotokopi Kartu Keluarga\n5. Pas Foto 3×4 berwarna\n6. Nomor Induk Siswa Nasional (NISN) — atau surat keterangan NISN dari sekolah asal",
            'documents_note'=> 'Semua dokumen dimasukkan ke dalam map warna biru dan diserahkan pada saat calon peserta didik mengikuti Ujian Seleksi Masuk di sekolah.',
            'guide'         => "1. Masuk ke halaman Pendaftaran SMP.\n2. Klik REGISTRASI untuk mendaftar akun, atau MASUK jika sudah pernah registrasi.\n3. Klik FORMULIR PENDAFTARAN untuk mengisi data calon peserta didik.\n4. Isi data dengan lengkap dan benar, kemudian klik SUBMIT.\n5. Setelah mengisi data, peserta akan mendapatkan Nomor Virtual Account dan Nomor Ujian untuk pembayaran Uang Pendaftaran.\n6. Paling lambat 3×24 jam setelah pembayaran, peserta akan menerima link Kartu Ujian.\n7. Kartu Ujian wajib dicetak dan dibawa saat Ujian Seleksi Masuk.\n8. Hasil Ujian Seleksi Masuk diumumkan secara online.\n9. Pendaftaran selesai.",
            'exam_date'     => '2026-06-12',
            'result_date'   => '2026-06-18 13:00',
            'application_url' => 'https://pmbsmp2.sutomo-mdn.sch.id/',
            'contact_email' => 'smpsutomo2@sutomo-mdn.sch.id',
            'contact_wa'    => '0813 6166 8828',
            'contact_phone' => '(061) 6615674',
            'contact_ig'    => 'sutomo2.medan',
            'address'       => 'Jl. Deli Indah IV No. 6, Pulo Brayan, Medan',
            'flyers'        => [],
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Header')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('headline')->required()->columnSpanFull(),
                    Forms\Components\Textarea::make('intro')->rows(2)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('A. Syarat Pendaftaran')
                ->schema([
                    Forms\Components\Textarea::make('requirements')->rows(4)->helperText('One rule per line.'),
                ]),

            Forms\Components\Section::make('B. Dokumen Persyaratan')
                ->schema([
                    Forms\Components\Textarea::make('documents')->rows(8)->helperText('One document per line.'),
                    Forms\Components\Textarea::make('documents_note')->rows(2)->label('NB / catatan'),
                ]),

            Forms\Components\Section::make('Panduan Pendaftaran Online')
                ->schema([
                    Forms\Components\Textarea::make('guide')->rows(10)->helperText('Numbered steps, one per line.'),
                ]),

            Forms\Components\Section::make('Key Dates')
                ->columns(3)
                ->schema([
                    Forms\Components\DatePicker::make('exam_date'),
                    Forms\Components\DateTimePicker::make('result_date'),
                    Forms\Components\TextInput::make('application_url')->label('Online application URL')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Hubungi Kami')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('contact_email')->email(),
                    Forms\Components\TextInput::make('contact_wa')->label('WhatsApp'),
                    Forms\Components\TextInput::make('contact_phone')->label('Phone'),
                    Forms\Components\TextInput::make('contact_ig')->label('Instagram'),
                    Forms\Components\Textarea::make('address')->rows(2)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Flyers / Photos')
                ->schema([
                    Forms\Components\FileUpload::make('flyers')
                        ->multiple()->reorderable()->maxFiles(8)->maxSize(8192)
                        ->disk('public')->directory('enrollment/flyers')
                        ->acceptedFileTypes(['image/*','application/pdf'])
                        ->helperText('Upload printable flyers, brochure pages or banner images. Visible to all enrollment officers.'),
                ]),
        ])->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save changes')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        Storage::put(self::STORAGE_PATH, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        Notification::make()->title('Enrollment info saved')->success()->send();
    }
}
