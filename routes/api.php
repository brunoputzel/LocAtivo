<?php

use App\Http\Controllers\Api\AtivoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ContratoController;
use App\Http\Controllers\Api\OrdemDeServicoController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // nomeados com prefixo api. pra não colidir com as rotas web homônimas
    // (ativos.index etc.) que servem as telas Livewire
    Route::apiResource('ativos', AtivoController::class)->names('api.ativos');
    Route::apiResource('clientes', ClienteController::class)->names('api.clientes');
    Route::apiResource('contratos', ContratoController::class)->only(['index', 'show', 'store'])->names('api.contratos');
    Route::patch('contratos/{contrato}/encerrar', [ContratoController::class, 'encerrar'])->name('api.contratos.encerrar');

    Route::get('contratos/{contrato}/checklists', [ChecklistController::class, 'index'])->name('api.contratos.checklists.index');
    Route::post('contratos/{contrato}/checklists', [ChecklistController::class, 'store'])->name('api.contratos.checklists.store');

    Route::apiResource('ordens-servico', OrdemDeServicoController::class)
        ->parameters(['ordens-servico' => 'ordemServico'])
        ->only(['index', 'show', 'store'])
        ->names('api.ordensServico');
    Route::patch('ordens-servico/{ordemServico}/fechar', [OrdemDeServicoController::class, 'fechar'])->name('api.ordensServico.fechar');

    Route::apiResource('usuarios', UsuarioController::class)->except(['show'])->names('api.usuarios');
    Route::patch('usuarios/{usuario}/ativar', [UsuarioController::class, 'ativar'])->name('api.usuarios.ativar');
});
