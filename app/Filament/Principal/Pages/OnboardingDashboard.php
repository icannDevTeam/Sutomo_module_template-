<?php

namespace App\Filament\Principal\Pages;

use App\Models\Application;
use Filament\Pages\Page;

class OnboardingDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?string $title = 'Onboarding';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.principal.pages.onboarding-dashboard';

    public function getViewData(): array
    {
        $steps = [
            'accepted'       => 'Accepted',
            'dev_fee'        => 'Development Fee',
            'books'          => 'Books Purchased',
            'class_assigned' => 'Class Assigned',
            'observing'      => 'Observing Attendance',
            'id_issued'      => 'ID Issued',
            'tuition'        => 'Tuition',
            'activated'      => 'Activated',
        ];
        $counts = [];
        foreach ($steps as $k => $label) {
            $counts[$k] = ['label' => $label, 'count' => Application::where('status', $k)->count()];
        }
        return ['steps' => $counts];
    }
}
