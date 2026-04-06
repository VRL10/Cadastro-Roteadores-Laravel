<?php

use App\Http\Controllers\API\MacAddressController;
use App\Http\Controllers\API\RelatorioController;
use App\Http\Controllers\API\ReparticaoController;
use App\Http\Controllers\API\RoteadorController;
use Illuminate\Support\Facades\Route;

Route::prefix('reparticoes')->group(function () {
    Route::get('/', [ReparticaoController::class, 'index']);
    Route::post('/', [ReparticaoController::class, 'store']);
    Route::put('/{reparticao}', [ReparticaoController::class, 'update']);
    Route::delete('/{reparticao}', [ReparticaoController::class, 'destroy']);
});
Route::get('/reparticoes/combo', [ReparticaoController::class, 'combo']);

Route::prefix('roteadores')->group(function () {
    Route::get('/', [RoteadorController::class, 'index']);
    Route::post('/', [RoteadorController::class, 'store']);
    Route::put('/{roteador}', [RoteadorController::class, 'update']);
    Route::delete('/{roteador}', [RoteadorController::class, 'destroy']);
});
Route::get('/roteadores/combo', [RoteadorController::class, 'combo']);
Route::get('/roteadores/ultimo', [RoteadorController::class, 'ultimo']);

Route::prefix('macs')->group(function () {
    Route::get('/', [MacAddressController::class, 'index']);
    Route::post('/', [MacAddressController::class, 'store']);
    Route::put('/{mac}', [MacAddressController::class, 'update']);
    Route::delete('/{mac}', [MacAddressController::class, 'destroy']);
});

Route::prefix('relatorios')->group(function () {
    Route::get('/reparticoes/pdf', [RelatorioController::class, 'reparticoesPdf']);
    Route::get('/reparticoes/excel', [RelatorioController::class, 'reparticoesExcel']);

    Route::get('/roteador/{ip}/pdf', [RelatorioController::class, 'roteadorPdf']);
    Route::get('/roteador/{ip}/excel', [RelatorioController::class, 'roteadorExcel']);

    Route::get('/macs/pdf', [RelatorioController::class, 'macsPdf']);
    Route::get('/macs/excel', [RelatorioController::class, 'macsExcel']);
});
