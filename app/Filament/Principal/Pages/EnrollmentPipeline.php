<?php

namespace App\Filament\Principal\Pages;

use App\Models\Application;
use Filament\Pages\Page;

class EnrollmentPipeline extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?string $title = 'Enrollment Pipeline';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.principal.pages.enrollment-pipeline';

    public function getViewData(): array
    {
        $stages = [
            'submitted'         => ['Submitted', 'gray'],
            'payment_confirmed' => ['Payment ✓', 'sky'],
            'exam_scheduled'    => ['Exam Set', 'sky'],
            'passed'            => ['Passed', 'emerald'],
            'accepted'          => ['Accepted', 'emerald'],
            'dev_fee'           => ['Dev Fee', 'amber'],
            'class_assigned'    => ['Class Assigned', 'indigo'],
            'observing'         => ['Observing', 'indigo'],
            'activated'         => ['Activated', 'emerald'],
        ];

        $columns = [];
        foreach ($stages as $key => [$label, $color]) {
            $columns[$key] = [
                'label'   => $label,
                'color'   => $color,
                'count'   => Application::where('status', $key)->count(),
                'records' => Application::where('status', $key)->latest('applied_at')->take(20)->get(),
            ];
        }
        return ['columns' => $columns];
    }
}
