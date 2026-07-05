<?php

namespace App\Models;

use App\Enums\StatusContrato;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;

    protected $fillable = [
        'ativo_id',
        'cliente_id',
        'data_inicio',
        'data_fim',
        'valor_diaria',
        'observacoes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'valor_diaria' => 'decimal:2',
            'status' => StatusContrato::class,
        ];
    }

    public function ativo()
    {
        return $this->belongsTo(Ativo::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cobranca()
    {
        return $this->hasOne(Cobranca::class);
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    public function checklistDeSaida(): ?Checklist
    {
        return $this->relationLoaded('checklists')
            ? $this->checklists->firstWhere('tipo', \App\Enums\TipoChecklist::SAIDA)
            : $this->checklists()->where('tipo', \App\Enums\TipoChecklist::SAIDA->value)->first();
    }

    /**
     * RF006: alerta D-3 - contrato ainda ativo com data_fim nos próximos 3 dias.
     */
    public function venceEmBreve(): bool
    {
        if ($this->status !== StatusContrato::ATIVO) {
            return false;
        }

        $hoje = now()->startOfDay();

        // usa diff absoluto (padrão) pra não depender da convenção de sinal do Carbon -
        // a checagem de "não venceu ainda" já fica isolada na comparação abaixo
        return $this->data_fim->gte($hoje) && $hoje->diffInDays($this->data_fim) <= 3;
    }
}
