<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisteredUserController; // Importado para o fluxo de registro
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Página Inicial / Login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('SistemaCadastro');
    }

    return Inertia::render('auth/Login', [
        'canResetPassword' => Route::has('password.request'),
        'status' => session('status'),
    ]);
});


// ROTAS COMPARTILHADAS
Route::middleware('auth')->group(function () {

    Route::get('/SistemaCadastro', function () {
        return Inertia::render('SistemaCadastro');
    })->name('SistemaCadastro');

    // Configurações de Perfil e Senha
    Route::get('/settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/settings/password', [PasswordController::class, 'update'])->name('password.update');
    
});


// ROTAS DO GESTOR
Route::middleware(['auth', 'can:isGestor'])->group(function () {
    
    Route::get('/gestor/dashboard', function () {
        return Inertia::render('Gestor/Dashboard');
    })->name('gestor.dashboard');

});


// ROTAS EXCLUSIVAS DO ADMINISTRADOR
Route::middleware(['auth', 'can:admin'])->group(function () {
    
    // Exibe o formulário de cadastro de usuários via Inertia
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

    // Processa a requisição de cadastro enviada pelo formulário
    Route::post('/register', [RegisteredUserController::class, 'store']);
    
});

// Arquivo nativo de autenticação (Login, Logout, etc.)
require __DIR__.'/auth.php';

// Rota de conveniência para desenvolvimento
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
