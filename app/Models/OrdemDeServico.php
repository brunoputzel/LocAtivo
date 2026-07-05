<?php

namespace App\Models;

use App\Enums\StatusOrdemServico;
use App\Enums\TipoOrdemServico;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemDeServico extends Model
{
    use HasFactory;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'ativo_id',
        'tecnico_id',
        'alerta_id',
        'tipo',
        'descricao',
        'data_abertura',
        'data_fechamento',
        'status',
        'custo',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoOrdemServico::class,
            'status' => StatusOrdemServico::class,
            'data_abertura' => 'date',
            'data_fechamento' => 'date',
            'custo' => 'decimal:2',
        ];
    }

    public function ativo()
    {
        return $this->belongsTo(Ativo::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function alerta()
    {
        return $this->belongsTo(AlertaManutencao::class, 'alerta_id');
    }
}
