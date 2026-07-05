<?php

namespace App\Livewire\Checklists;

use App\Models\Checklist;
use App\Models\Contrato;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Checklists')]
class ChecklistsIndex extends Component
{
    public string $contratoId = '';

    public function render()
    {
        $checklists = Checklist::query()
            ->when($this->contratoId, fn ($query) => $query->where('contrato_id', $this->contratoId))
            ->with(['contrato.ativo', 'contrato.cliente', 'usuario'])
            ->latest()
            ->get();

        return view('livewire.checklists.checklists-index', [
            'checklists' => $checklists,
            'contratos' => Contrato::query()->with(['ativo', 'cliente'])->latest()->get(),
            'existemChecklists' => Checklist::query()->exists(),
        ]);
    }
}
