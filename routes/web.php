<?php

use Illuminate\Support\Facades\Route;

// O Inertia conecta Laravel com Vue sem precisar criar API separada.
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('SistemaCadastro');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

// Aqui estão as rotas para as páginas de configurações e autenticação.
require __DIR__.'/settings.php';
