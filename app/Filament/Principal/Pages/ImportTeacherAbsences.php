<?php

namespace App\Filament\Principal\Pages;

use App\Models\TeacherLeave;
use App\Support\LeaveImportParser;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportTeacherAbsences extends Page
{
    use WithFileUploads;

    protected static ?string $navigationGroup  = 'Leave & Substitution';
    protected static ?string $navigationIcon   = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel  = 'Import Absences';
    protected static ?string $title            = 'Import Teacher Absences';
    protected static ?int    $navigationSort   = 10;
    protected static string  $view             = 'filament.principal.pages.import-teacher-absences';
    protected static ?string $slug             = 'import-teacher-absences';

    public $upload = null;

    /** @var Collection|null Parsed rows (after upload) */
    public ?array $rows = null;

    public ?string $parseError = null;
    public ?string $importResult = null;
    public bool $imported = false;

    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            echo LeaveImportParser::templateCsv();
        }, 'template-absensi-guru.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function updatedUpload(): void
    {
        $this->rows        = null;
        $this->parseError  = null;
        $this->importResult = null;
        $this->imported    = false;

        if (! $this->upload) {
            return;
        }

        try {
            $path      = $this->upload->getRealPath();
            $extension = strtolower($this->upload->getClientOriginalExtension());

            if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
                $this->parseError = 'Only .xlsx, .xls, and .csv files are supported.';
                return;
            }

            $parsed      = LeaveImportParser::parse($path, $extension);
            $this->rows  = $parsed->values()->all();
        } catch (\Throwable $e) {
            $this->parseError = 'Could not parse the file: ' . $e->getMessage();
        }
    }

    public function confirmImport(): void
    {
        if (! $this->rows) {
            return;
        }

        $created  = 0;
        $skipped  = 0;

        foreach ($this->rows as $row) {
            if (! $row['valid'] || ! $row['teacher_id'] || ! $row['date'] || ! $row['type']) {
                $skipped++;
                continue;
            }

            // Skip true duplicates (same teacher + same date + same type already exists).
            $exists = TeacherLeave::query()
                ->where('teacher_id', $row['teacher_id'])
                ->whereDate('starts_at', $row['date'])
                ->where('type', $row['type'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $reasonParts = array_filter([
                $row['reason'],
                $row['period'] ? 'Jam ke: ' . $row['period'] : null,
                $row['class']  ? 'Kelas: ' . $row['class']   : null,
            ]);

            TeacherLeave::create([
                'teacher_id'            => $row['teacher_id'],
                'type'                  => $row['type'],
                'starts_at'             => $row['date'],
                'ends_at'               => $row['date'],
                'reason'                => implode(' · ', $reasonParts) ?: 'Imported via bulk upload',
                'status'                => 'approved',
                'substitute_teacher_id' => $row['substitute_id'] ?: null,
                'decided_by'            => $row['decided_by'] ?: 'Bulk Import',
                'decided_at'            => now(),
                'auto_search_enabled'   => false,
                'auto_search_status'    => 'disabled',
            ]);

            $created++;
        }

        $this->importResult = "Import complete: {$created} record(s) created, {$skipped} skipped (duplicate or invalid).";
        $this->imported     = true;
        $this->upload       = null;
    }

    public function resetImport(): void
    {
        $this->rows         = null;
        $this->upload       = null;
        $this->parseError   = null;
        $this->importResult = null;
        $this->imported     = false;
    }
}
