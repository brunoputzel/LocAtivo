<?php

use App\Livewire\Ativos\AtivosIndex;
use App\Livewire\Checklists\ChecklistForm;
use App\Livewire\Checklists\ChecklistsIndex;
use App\Livewire\Clientes\ClientesIndex;
use App\Livewire\Clientes\ClienteShow;
use App\Livewire\Contratos\ContratosIndex;
use App\Livewire\Manutencao\ManutencaoIndex;
use App\Livewire\Usuarios\UsuariosIndex;
use App\Models\Ativo;
use App\Models\Checklist;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\OrdemDeServico;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// sem landing page própria - o "/" só decide pra onde mandar quem chegou aqui
Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('ativos', AtivosIndex::class)
        ->middleware('can:viewAny,'.Ativo::class)
        ->name('ativos.index');

    Route::get('clientes', ClientesIndex::class)
        ->middleware('can:viewAny,'.Cliente::class)
        ->name('clientes.index');

    Route::get('clientes/{cliente}', ClienteShow::class)
        ->middleware('can:view,cliente')
        ->name('clientes.show');

    Route::get('contratos', ContratosIndex::class)
        ->middleware('can:viewAny,'.Contrato::class)
        ->name('contratos.index');

    Route::get('checklists', ChecklistsIndex::class)
        ->middleware('can:viewAny,'.Checklist::class)
        ->name('checklists.index');

    Route::get('contratos/{contrato}/checklists/{tipo}', ChecklistForm::class)
        ->whereIn('tipo', ['saida', 'retorno'])
        ->middleware('can:create,'.Checklist::class)
        ->name('checklists.form');

    Route::get('manutencao', ManutencaoIndex::class)
        ->middleware('can:viewAny,'.OrdemDeServico::class)
        ->name('manutencao.index');

    Route::get('usuarios', UsuariosIndex::class)
        ->middleware('can:viewAny,'.User::class)
        ->name('usuarios.index');
});

require __DIR__.'/auth.php';
