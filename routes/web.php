<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/enrollment-config/{unit}', [\App\Http\Controllers\Api\EnrollmentConfigController::class, 'show'])
    ->whereAlphaNumeric('unit')
    ->name('api.enrollment-config.show');
