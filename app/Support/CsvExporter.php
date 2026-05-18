<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    /**
     * Stream a CSV download.
     *
     * @param  iterable $rows      Eloquent collection / array of models or arrays
     * @param  array    $columns   ['header' => 'attribute' | Closure(row): mixed]
     * @param  string   $filename  e.g. 'candidates-2026-05-18.csv'
     */
    public static function download(iterable $rows, array $columns, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows, $columns) {
            $out = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, array_keys($columns));

            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $accessor) {
                    $value = $accessor instanceof Closure
                        ? $accessor($row)
                        : data_get($row, $accessor);

                    if (is_array($value)) {
                        $value = implode('; ', array_map(
                            fn ($v) => is_scalar($v) ? (string) $v : json_encode($v),
                            $value,
                        ));
                    } elseif ($value instanceof \DateTimeInterface) {
                        $value = $value->format('Y-m-d H:i');
                    } elseif (is_bool($value)) {
                        $value = $value ? 'Yes' : 'No';
                    }

                    $line[] = (string) ($value ?? '');
                }
                fputcsv($out, $line);
            }

            fclose($out);
        }, $filename, [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public static function filename(string $base): string
    {
        return $base . '-' . now()->format('Y-m-d-His') . '.csv';
    }
}
