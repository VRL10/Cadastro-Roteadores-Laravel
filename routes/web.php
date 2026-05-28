<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('SistemaCadastro');
    }

    return Inertia::render('auth/Login', [
        'canResetPassword' => Route::has('password.request'),
        'status' => session('status'),
    ]);
});

Route::middleware('auth')->group(function () {

    Route::get('/settings/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/settings/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/settings/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/settings/password', [PasswordController::class, 'edit'])
        ->name('password.edit');

    Route::put('/settings/password', [PasswordController::class, 'update'])
        ->name('password.update');
});

Route::get('/SistemaCadastro', function () {
    return Inertia::render('SistemaCadastro');
})->middleware('auth')->name('SistemaCadastro');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Rota de conveniência para desenvolvimento: loga como `victor@gmail.com` e redireciona.
if (app()->environment('local')) {
    Route::get('/login-as-victor', function () {
        $user = \App\Models\User::where('email', 'victor@gmail.com')->first();
        if (! $user) {
            abort(404, 'User not found');
        }
        auth()->login($user);
        return redirect()->route('SistemaCadastro');
    });
}
