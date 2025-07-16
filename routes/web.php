<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    // Route::get('/dashboard', function () {
    //     return Inertia::render('Dashboard');
    // })->name('dashboard');

    // Dashboard con datos reales
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // Rutas para profesionales
    Route::resource('professionals', ProfessionalController::class);

    // Rutas para especialidades
    Route::resource('specialties', SpecialtyController::class);

    // Rutas para pacientes
    Route::resource('patients', PatientController::class);

    // Rutas para citas
    Route::resource('appointments', AppointmentController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
