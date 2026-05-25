<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/enrollment-config/{unit}', [\App\Http\Controllers\Api\EnrollmentConfigController::class, 'show'])
    ->whereAlphaNumeric('unit')
    ->name('api.enrollment-config.show');

// Printable views (require authenticated Filament panel session)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/print/teacher-leave/{record}', function (\App\Models\TeacherLeave $record) {
        return view('print.teacher-leave-letter', ['record' => $record->load('teacher', 'substitute')]);
    })->name('teacher-leave.print');

    Route::get('/print/duty-assignment/{record}', function (\App\Models\DutyAssignment $record) {
        return view('print.duty-briefing', ['record' => $record->load('teacher')]);
    })->name('duty-assignment.print');
});
