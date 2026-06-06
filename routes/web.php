<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/enrollment-config/{unit}', [\App\Http\Controllers\Api\EnrollmentConfigController::class, 'show'])
    ->whereAlphaNumeric('unit')
    ->name('api.enrollment-config.show');

// Public signed routes for substitute cover offers (no auth — token in URL).
Route::middleware('signed')->group(function () {
    Route::get('/substitute-offer/{offer}/accept', [\App\Http\Controllers\SubstituteOfferController::class, 'accept'])
        ->name('substitute-offer.accept');
    Route::get('/substitute-offer/{offer}/decline', [\App\Http\Controllers\SubstituteOfferController::class, 'decline'])
        ->name('substitute-offer.decline');
});

// Printable views (require authenticated Filament panel session)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/print/teacher-leave/{record}', function (\App\Models\TeacherLeave $record) {
        return view('print.teacher-leave-letter', ['record' => $record->load('teacher', 'substitute')]);
    })->name('teacher-leave.print');

        Route::get('/print/letter-of-intent/{record}', function (\App\Models\LetterOfIntent $record) {
            $record->load(['teacher', 'principal', 'teacherContract']);

            $payload = ['record' => $record];

            if (request()->boolean('download')) {
                $html = view('print.letter-of-intent', $payload)->render();

                return response()->streamDownload(
                    function () use ($html): void {
                        echo $html;
                    },
                    'letter-of-intent-' . $record->id . '.html',
                    ['Content-Type' => 'text/html; charset=UTF-8']
                );
            }

            return view('print.letter-of-intent', $payload);
        })->name('letter-of-intent.print');

    Route::get('/print/duty-assignment/{record}', function (\App\Models\DutyAssignment $record) {
        return view('print.duty-briefing', ['record' => $record->load('teacher')]);
    })->name('duty-assignment.print');

    Route::get('/observations/{observation}/print', function (\App\Models\TeacherObservation $observation) {
        abort_unless(in_array(auth()->user()->role ?? '', \App\Models\User::PRINCIPAL_ROLES, true), 403);
        return view('print.observation', ['o' => $observation->load(['teacher', 'observer', 'reviewer'])]);
    })->name('observations.print');
});
