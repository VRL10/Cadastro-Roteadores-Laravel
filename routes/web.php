<?php

use Illuminate\Support\Facades\Route;

// O Inertia conecta Laravel com Vue sem precisar criar API separada.
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('SistemaCadastro');
})->name('home');
