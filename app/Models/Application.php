<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $guarded = [];

    protected $casts = [
        'dob'         => 'date',
        'applied_at'  => 'date',
        'exam_date'   => 'date',
        'waitlisted'  => 'boolean',
        'meta'        => 'array',
    ];

    public const STATUSES = [
        'submitted'         => 'Submitted',
        'payment_confirmed' => 'Payment Confirmed',
        'exam_scheduled'    => 'Placement Exam Scheduled',
        'passed'            => 'Passed',
        'failed'            => 'Failed',
        'waitlisted'        => 'Waitlisted',
        'accepted'          => 'Accepted',
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
        'accepted' => 'success', 'dev_fee' => 'warning',
        'books' => 'warning', 'class_assigned' => 'info',
        'observing' => 'primary', 'id_issued' => 'info',
        'tuition' => 'warning', 'activated' => 'success',
        'withdrawn' => 'danger',
    ];

    public const PIPELINE = [
        'submitted', 'payment_confirmed', 'exam_scheduled',
        'passed', 'accepted', 'dev_fee', 'books',
        'class_assigned', 'observing', 'id_issued', 'tuition', 'activated',
    ];
}
