<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentPeriod;
use App\Support\VirtualAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class EnrollmentConfigController extends Controller
{
    /**
     * GET /api/enrollment-config/{unit}
     *
     * Returns the public enrollment configuration for one unit so the
     * front-end /apply wizard can render fee + VA scheme without
     * hard-coding values.
     */
    public function show(string $unit): JsonResponse
    {
        $unit = strtolower($unit);
        $unitDigit = VirtualAccount::unitDigit($unit);

        if ($unitDigit === null) {
            return response()->json(['error' => 'Unknown unit'], 404);
        }

        $payload = Cache::remember("enrollment-config:{$unit}", 60, function () use ($unit, $unitDigit) {
            // EnrollmentPeriod stores the unit slug in the `campus` column.
            $period = EnrollmentPeriod::query()
                ->where('campus', $unit)
                ->whereIn('status', ['open', 'draft'])
                ->orderByDesc('opens_at')
                ->first();

            return [
                'unit'                  => $unit,
                'period_id'             => $period?->id,
                'period_name'           => $period?->name,
                'status'                => $period?->status ?? 'closed',
                'opens_at'              => $period?->opens_at?->toIso8601String(),
                'closes_at'             => $period?->closes_at?->toIso8601String(),
                'application_fee'       => (int) ($period?->application_fee ?? 300000),
                'payment_expiry_hours'  => (int) ($period?->payment_expiry_hours ?? 24),
                'va' => [
                    'bank'              => 'BCA',
                    'bank_prefix'       => VirtualAccount::BANK_PREFIX,
                    'unit_digit'        => $unitDigit,
                    'purpose_digit'     => VirtualAccount::purposeDigit('enrollment'),
                    'format_hint'       => VirtualAccount::BANK_PREFIX . $unitDigit . VirtualAccount::purposeDigit('enrollment') . '<reference>',
                ],
            ];
        });

        return response()->json($payload);
    }
}
