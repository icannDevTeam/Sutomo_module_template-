<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $guarded = [];

    protected $casts = [
        'dob'                 => 'date',
        'applied_at'          => 'date',
        'exam_date'           => 'date',
        'waitlisted'          => 'boolean',
        'is_teacher_child'    => 'boolean',
        'is_existing_student' => 'boolean',
        'payment_amount'      => 'decimal:2',
        'payment_paid_at'     => 'datetime',
        'placement_score'     => 'decimal:2',
        'va_issued_at'        => 'datetime',
        'va_expires_at'       => 'datetime',
        'submitted_at'        => 'datetime',
        'meta'                => 'array',
    ];

    /** Applications eligible to sit / be scheduled for the placement exam. */
    public const ELIGIBLE_FOR_EXAM = ['exam_scheduled', 'submitted', 'payment_confirmed', 'waitlisted'];

    /** Post-exam decision outcomes. */
    public const POST_EXAM_OUTCOMES = ['passed', 'failed', 'waitlisted'];

    /** Statuses that indicate the candidate has been accepted and is moving through onboarding. */
    public const ONBOARDING_STATUSES = [
        'accepted', 'dev_fee', 'books', 'class_assigned',
        'observing', 'id_issued', 'tuition', 'activated',
    ];

    /** Terminal exit statuses (no further onboarding actions). */
    public const TERMINAL_STATUSES = ['withdrawn', 'declined'];

    public const STATUSES = [
        'submitted'         => 'Submitted',
        'payment_confirmed' => 'Payment Confirmed',
        'exam_scheduled'    => 'Placement Exam Scheduled',
        'passed'            => 'Passed',
        'failed'            => 'Failed',
        'waitlisted'        => 'Waitlisted',
        'accepted'          => 'Accepted',
        'declined'          => 'Declined',
        'dev_fee'           => 'Development Fee',
        'books'             => 'Book Purchase',
        'class_assigned'    => 'Class Assigned',
        'observing'         => 'Attendance Observation',
        'id_issued'         => 'ID Issued',
        'tuition'           => 'Tuition',
        'activated'         => 'Activated',
        'withdrawn'         => 'Withdrawn',
    ];

    public const STATUS_COLORS = [
        'submitted' => 'gray', 'payment_confirmed' => 'info',
        'exam_scheduled' => 'info', 'passed' => 'success',
        'failed' => 'danger', 'waitlisted' => 'warning',
        'accepted' => 'success', 'declined' => 'danger',
        'dev_fee' => 'warning', 'books' => 'warning',
        'class_assigned' => 'info', 'observing' => 'primary',
        'id_issued' => 'info', 'tuition' => 'warning',
        'activated' => 'success', 'withdrawn' => 'danger',
    ];

    public const PIPELINE = [
        'submitted', 'payment_confirmed', 'exam_scheduled',
        'passed', 'accepted', 'dev_fee', 'books',
        'class_assigned', 'observing', 'id_issued', 'tuition', 'activated',
    ];

    public const KANBAN_STAGES = [
        'submitted', 'payment_confirmed', 'exam_scheduled',
        'passed', 'waitlisted', 'failed', 'accepted', 'declined',
    ];

    public const PAYMENT_METHODS = [
        'transfer' => 'Bank Transfer',
        'va'       => 'Virtual Account',
        'cash'     => 'Cash',
        'qris'     => 'QRIS',
    ];

    public const ORPHAN_STATUSES = [
        'none'         => 'Bukan Yatim/Piatu',
        'yatim'        => 'Yatim',
        'piatu'        => 'Piatu',
        'yatim_piatu'  => 'Yatim Piatu',
    ];

    public const APPLICANT_TYPES = [
        'new'       => 'New Student',
        'existing'  => 'Existing Student',
        'returning' => 'Returning Student',
    ];

    /** Coerce legacy values (transfer/sibling) into the 3-value taxonomy. */
    public static function applicantTypeLabel(?string $value): string
    {
        return match ($value) {
            'new'                              => self::APPLICANT_TYPES['new'],
            'returning', 'transfer', 'sibling' => self::APPLICANT_TYPES['returning'],
            default                            => self::APPLICANT_TYPES['existing'],
        };
    }

    public function enrollmentPeriod(): BelongsTo
    {
        return $this->belongsTo(EnrollmentPeriod::class);
    }

    /**
     * Generate and persist the invoice number if one has not been issued yet.
     * Returns the (now guaranteed) invoice number.
     */
    public function ensureInvoiceNo(): string
    {
        if (empty($this->invoice_no)) {
            $this->invoice_no = 'INV-' . now()->format('Y') . '-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
            $this->save();
        }
        return $this->invoice_no;
    }

    public function applyExamScore(?float $score): string
    {
        $this->placement_score = $score;
        $period = $this->enrollmentPeriod;
        $pass = $period?->pass_threshold ?? 70;
        $fail = $period?->fail_threshold ?? 50;

        if ($score === null) {
            $this->save();
            return $this->status;
        }
        if ($score >= $pass) {
            $this->status = 'passed';
        } elseif ($score < $fail) {
            $this->status = 'failed';
        } else {
            // Middle band (fail_threshold <= score < pass_threshold) -> waitlist for manual review.
            $this->status = 'waitlisted';
            $this->waitlisted = true;
        }
        $this->save();
        return $this->status;
    }
}

