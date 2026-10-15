<?php

/*
|--------------------------------------------------------------------------
| Yayasan Approval Portal — UI thresholds and defaults
|--------------------------------------------------------------------------
| All ageing / SLA values that drive visual urgency live here so Blade and
| the presenter never hard-code day counts.
*/

return [

    // Days a submission may wait for a Yayasan decision before it is flagged.
    'review_attention_days' => (int) env('APPROVALS_REVIEW_ATTENTION_DAYS', 5),
    'review_overdue_days'   => (int) env('APPROVALS_REVIEW_OVERDUE_DAYS', 10),

    // Days an approved request may sit without an HR assignee.
    'assign_attention_days' => (int) env('APPROVALS_ASSIGN_ATTENTION_DAYS', 2),
    'assign_overdue_days'   => (int) env('APPROVALS_ASSIGN_OVERDUE_DAYS', 5),

    // Days a principal may leave an uploaded document unreviewed.
    'principal_review_attention_days' => (int) env('APPROVALS_PRINCIPAL_REVIEW_ATTENTION_DAYS', 3),
    'principal_review_overdue_days'   => (int) env('APPROVALS_PRINCIPAL_REVIEW_OVERDUE_DAYS', 7),

    // Default HR preparation window (working days) when Yayasan assigns a task.
    'hr_default_due_days' => (int) env('APPROVALS_HR_DEFAULT_DUE_DAYS', 7),

    // Minimum characters for a required reason / note.
    'reason_min_length' => 20,

    // Minimum seconds between "nudge" reminders to the same assignee per request.
    'nudge_cooldown_minutes' => 240,

    // Queue page size for cursor pagination.
    'page_size' => 20,

    // Private disk used for contract document versions.
    'document_disk' => env('APPROVALS_DOCUMENT_DISK', 'local'),
];
