<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProfessionalController;

Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Rutas para profesionales
    Route::resource('professionals', ProfessionalController::class);

    // Rutas para especialidades
    Route::resource('specialties', App\Http\Controllers\SpecialtyController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
