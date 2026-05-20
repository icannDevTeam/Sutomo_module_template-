<?php

namespace App\Models;

use App\Support\VirtualAccount;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'bool',
    ];

    /**
     * Aligned with App\Support\VirtualAccount::PURPOSE_DIGITS so every row
     * can derive its canonical 14339-XY prefix automatically.
     */
    public const PURPOSES = [
        'tuition'    => 'School Tuition',
        'admin'      => 'Administration Fee',
        'books'      => 'Textbook Purchase',
        'dev_fee'    => 'School Development Fee',
        'enrollment' => 'Enrollment Fee',
    ];

    /** Unit slug → human label (matches VirtualAccount::UNIT_DIGITS keys). */
    public const UNITS = [
        'sma'         => 'SMA',
        'smp'         => 'SMP',
        'sd'          => 'SD',
        'tk'          => 'TK',
        'playgroup'   => 'Playgroup',
        'pre_nursery' => 'Pre-Nursery',
    ];

    /**
     * Canonical VA prefix `14339 + unitDigit + purposeDigit` for this row.
     * The <ref> tail (NIS for tuition / registration # for enrollment) is
     * appended at payment time by the onboarding flow.
     */
    public function getVaPrefixComputedAttribute(): ?string
    {
        $u = VirtualAccount::unitDigit($this->unit);
        $p = VirtualAccount::purposeDigit($this->purpose);
        return ($u && $p) ? VirtualAccount::BANK_PREFIX . $u . $p : null;
    }
}
