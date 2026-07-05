<?php

namespace App\Livewire\Layout;

use App\Enums\PerfilUsuario;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class NavegacaoLateral extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    /**
     * Único lugar que decide os itens do menu por perfil - novas telas só
     * precisam entrar nesta lista, não em cada view que renderiza o menu.
     *
     * @return array<int, array{rota: string, label: string, perfis: array<int, PerfilUsuario>}>
     */
    private static function itens(): array
    {
        return [
            // Painel e Financeiro ocultos temporariamente - fora do escopo desta
            // entrega. Rota e componente Livewire continuam existindo e acessíveis
            // por URL direta; só a entrada do menu foi removida.
            // ['rota' => 'dashboard', 'label' => 'Painel', 'perfis' => PerfilUsuario::cases()],
            ['rota' => 'ativos.index', 'label' => 'Ativos', 'perfis' => [
                PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO, PerfilUsuario::TECNICO_MANUTENCAO, PerfilUsuario::FINANCEIRO,
            ]],
            ['rota' => 'clientes.index', 'label' => 'Clientes', 'perfis' => [
                PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO,
            ]],
            ['rota' => 'contratos.index', 'label' => 'Contratos', 'perfis' => [
                PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO, PerfilUsuario::CLIENTE,
            ]],
            ['rota' => 'checklists.index', 'label' => 'Checklists', 'perfis' => [
                PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO,
            ]],
            ['rota' => 'manutencao.index', 'label' => 'Manutenção', 'perfis' => [
                PerfilUsuario::GESTOR, PerfilUsuario::TECNICO_MANUTENCAO,
            ]],
            // ['rota' => 'financeiro.index', 'label' => 'Financeiro', 'perfis' => [
            //     PerfilUsuario::GESTOR, PerfilUsuario::FINANCEIRO,
            // ]],
            ['rota' => 'usuarios.index', 'label' => 'Usuários', 'perfis' => [
                PerfilUsuario::GESTOR,
            ]],
        ];
    }

    /**
     * A rota de destino pode não existir ainda (entidade de fase futura) - o item
     * aparece mesmo assim, só sem link funcional até a tela correspondente existir.
     *
     * @return array<int, array{rota: string, label: string, href: string}>
     */
    public function itensMenu(): array
    {
        $perfil = auth()->user()->perfil;

        return collect(self::itens())
            ->filter(fn (array $item) => in_array($perfil, $item['perfis'], true))
            ->map(fn (array $item) => [
                'rota' => $item['rota'],
                'label' => $item['label'],
                'href' => Route::has($item['rota']) ? route($item['rota']) : '#',
            ])
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.layout.navegacao-lateral', [
            'itens' => $this->itensMenu(),
        ]);
    }
}
