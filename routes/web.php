<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/download/latest', [LandingController::class, 'downloadLatest'])->name('download.latest');
Route::get('/download/{release}', [LandingController::class, 'download'])->name('download.release');
