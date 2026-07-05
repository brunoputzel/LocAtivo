<?php

namespace App\Models;

use App\Enums\TipoAlerta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertaManutencao extends Model
{
    use HasFactory;

    protected $table = 'alertas_manutencao';

    protected $fillable = [
        'ativo_id',
        'tipo',
        'descricao',
        'data_alerta',
        'resolvido',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoAlerta::class,
            'data_alerta' => 'date',
            'resolvido' => 'boolean',
        ];
    }

    public function ativo()
    {
        return $this->belongsTo(Ativo::class);
    }

    public function ordensDeServico()
    {
        return $this->hasMany(OrdemDeServico::class, 'alerta_id');
    }
}
