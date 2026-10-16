<?php

namespace App\Support;

use App\Models\Teacher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LeaveImportParser
{
    /** Indonesian / common text → leave type key */
    private const TYPE_MAP = [
        'sakit'          => 'sick',
        'sick'           => 'sick',
        'sick leave'     => 'sick',
        'darurat'        => 'emergency',
        'emergency'      => 'emergency',
        'sabbatical'     => 'sabbatical',
        'dispensasi'     => 'sabbatical',
        'izin'           => 'prior',
        'ijin'           => 'prior',
        'prior'          => 'prior',
        'prior notice'   => 'prior',
        'setengah hari'  => 'midday',
        'midday'         => 'midday',
        'mid-day'        => 'midday',
        'tengah hari'    => 'midday',
        'singkat'        => 'brief_absence',
        'brief'          => 'brief_absence',
        'brief absence'  => 'brief_absence',
        'tugas luar'     => 'assigned_work',
        'assigned'       => 'assigned_work',
        'assigned work'  => 'assigned_work',
        'duka'           => 'bereavement',
        'berkabung'      => 'bereavement',
        'bereavement'    => 'bereavement',
        'nikah'          => 'marriage',
        'pernikahan'     => 'marriage',
        'marriage'       => 'marriage',
    ];

    /** Expected header aliases → canonical key */
    private const HEADER_MAP = [
        'tanggal'         => 'date',
        'date'            => 'date',
        'tgl'             => 'date',
        'nama guru'       => 'teacher',
        'nama'            => 'teacher',
        'teacher'         => 'teacher',
        'guru'            => 'teacher',
        'jenis ijin'      => 'type',
        'jenis izin'      => 'type',
        'jenis'           => 'type',
        'type'            => 'type',
        'tipe'            => 'type',
        'alasan'          => 'reason',
        'reason'          => 'reason',
        'keterangan'      => 'reason',
        'jam ke'          => 'period',
        'jam'             => 'period',
        'periode'         => 'period',
        'period'          => 'period',
        'kelas'           => 'class',
        'class'           => 'class',
        'guru ganti'      => 'substitute',
        'substitute'      => 'substitute',
        'pengganti'       => 'substitute',
        'inval'           => 'substitute',
        'disetujui oleh'  => 'decided_by',
        'approved by'     => 'decided_by',
        'disetujui'       => 'decided_by',
    ];

    /**
     * Parse an uploaded file (xlsx or csv).
     * Returns a Collection of row arrays, each containing:
     *   raw, parsed (date, teacher_id, teacher_name, type, reason, period, class, substitute_id, substitute_name, decided_by),
     *   errors (array of string messages), valid (bool)
     */
    public static function parse(string $path, string $extension): Collection
    {
        $rows = match (strtolower($extension)) {
            'csv'  => static::parseCsv($path),
            default => static::parseXlsx($path),
        };

        $teacherIndex  = static::buildTeacherIndex();

        return collect($rows)->map(function (array $raw, int $i) use ($teacherIndex) {
            return static::validateRow($raw, $i + 1, $teacherIndex);
        })->filter(fn ($r) => ! empty(array_filter($r['raw']))); // drop fully-blank rows
    }

    /** Build a name→id + name index for fuzzy matching */
    private static function buildTeacherIndex(): array
    {
        return Teacher::query()
            ->select(['id', 'name', 'code'])
            ->get()
            ->flatMap(function (Teacher $t) {
                $normalized = static::normName($t->name);
                return [
                    $normalized => $t,
                    // Also index without title suffixes (S.Pd, M.Pd, etc.)
                    preg_replace('/,?\s+(s\.|m\.|dr\.|drs\.|spd|mpd|mpsi|sse).*$/i', '', $normalized) => $t,
                ];
            })
            ->all();
    }

    private static function normName(string $name): string
    {
        return Str::lower(preg_replace('/\s+/', ' ', trim($name)));
    }

    private static function findTeacher(string $raw, array $index): ?Teacher
    {
        if (! $raw) {
            return null;
        }
        $norm = static::normName($raw);
        if (isset($index[$norm])) {
            return $index[$norm];
        }
        // Partial match: find index key that contains or is contained by input
        foreach ($index as $key => $teacher) {
            if (str_contains($norm, $key) || str_contains($key, $norm)) {
                return $teacher;
            }
        }
        return null;
    }

    private static function resolveType(string $raw): ?string
    {
        $norm = Str::lower(trim($raw));
        return static::TYPE_MAP[$norm] ?? null;
    }

    private static function resolveDate(mixed $raw): ?string
    {
        if (! $raw) {
            return null;
        }
        // Excel serial number
        if (is_numeric($raw) && $raw > 1000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw)
                    ->format('Y-m-d');
            } catch (\Throwable) {}
        }
        $s = trim((string) $raw);
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd M Y', 'd F Y'] as $fmt) {
            try {
                $d = \DateTime::createFromFormat($fmt, $s);
                if ($d && $d->format($fmt) === $s) {
                    return $d->format('Y-m-d');
                }
            } catch (\Throwable) {}
        }
        // last resort: Carbon
        try {
            return CarbonImmutable::parse($s)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function validateRow(array $raw, int $rowNum, array $teacherIndex): array
    {
        $errors = [];

        // Date
        $date = static::resolveDate($raw['date'] ?? null);
        if (! $date) {
            $errors[] = 'Date missing or unrecognised';
        }

        // Teacher
        $teacher = static::findTeacher($raw['teacher'] ?? '', $teacherIndex);
        if (! $teacher) {
            $errors[] = 'Teacher "' . ($raw['teacher'] ?? '—') . '" not found in system';
        }

        // Type
        $type = static::resolveType($raw['type'] ?? '');
        if (! $type) {
            $errors[] = 'Leave type "' . ($raw['type'] ?? '—') . '" not recognised';
        }

        // Optional substitute
        $substitute = null;
        if (! empty($raw['substitute'])) {
            $substitute = static::findTeacher($raw['substitute'], $teacherIndex);
            if (! $substitute) {
                $errors[] = 'Substitute "' . $raw['substitute'] . '" not found (row will import without substitute)';
            }
        }

        return [
            'row'              => $rowNum,
            'raw'              => $raw,
            'date'             => $date,
            'teacher_id'       => $teacher?->id,
            'teacher_name'     => $teacher?->name ?? ($raw['teacher'] ?? ''),
            'type'             => $type,
            'reason'           => trim($raw['reason'] ?? ''),
            'period'           => trim($raw['period'] ?? ''),
            'class'            => trim($raw['class'] ?? ''),
            'substitute_id'    => $substitute?->id,
            'substitute_name'  => $substitute?->name ?? ($raw['substitute'] ?? ''),
            'decided_by'       => trim($raw['decided_by'] ?? ''),
            'errors'           => $errors,
            'valid'            => empty($errors) || (count($errors) === 1 && str_contains($errors[0], 'not found (row')),
        ];
    }

    // ---------- file readers ----------

    private static function parseXlsx(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $data  = $sheet->toArray(null, true, true, false);

        if (empty($data)) {
            return [];
        }

        $headers = static::mapHeaders(array_shift($data));
        $rows    = [];

        foreach ($data as $raw) {
            $row = [];
            foreach ($headers as $idx => $key) {
                if ($key) {
                    $row[$key] = $raw[$idx] ?? null;
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private static function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        $headerRaw = fgetcsv($handle, 0, ',', '"', '\\');
        if (! $headerRaw) {
            fclose($handle);
            return [];
        }

        $headers = static::mapHeaders($headerRaw);
        $rows    = [];

        while (($cols = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $row = [];
            foreach ($headers as $idx => $key) {
                if ($key) {
                    $row[$key] = $cols[$idx] ?? null;
                }
            }
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /** Map a raw header row to canonical keys (unknown cols → null) */
    private static function mapHeaders(array $raw): array
    {
        return array_map(function ($cell) {
            // Strip UTF-8 BOM that Excel/fgetcsv may prepend to the first cell.
            $norm = Str::lower(trim(ltrim((string) $cell, "\xEF\xBB\xBF")));
            return static::HEADER_MAP[$norm] ?? null;
        }, $raw);
    }

    /** Generate a CSV template as a string */
    public static function templateCsv(): string
    {
        $lines = [
            ['Tanggal', 'Nama Guru', 'Jenis Ijin', 'Alasan', 'Jam Ke', 'Kelas', 'Guru Ganti', 'Disetujui Oleh'],
            ['16/10/2026', 'Budi Santoso, S.Pd', 'Sakit', 'Demam', '1,2,3', '8-A', '', 'Pak Hartono'],
            ['16/10/2026', 'Rina Dewi, M.Pd', 'Darurat', 'Keluarga', '4,5', '9-B', 'Ahmad Yani, S.Pd', 'Pak Hartono'],
        ];

        ob_start();
        $f = fopen('php://output', 'w');
        // BOM for Excel to recognise UTF-8
        fwrite($f, "\xEF\xBB\xBF");
        foreach ($lines as $row) {
            fputcsv($f, $row, ',', '"', '\\');
        }
        fclose($f);

        return ob_get_clean();
    }
}
