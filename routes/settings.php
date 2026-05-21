<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rotas de configuração do sistema (públicas)
Route::get('settings/appearance', function () {
    return Inertia::render('settings/Appearance');
})->name('appearance');
