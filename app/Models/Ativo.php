<?php

namespace App\Models;

use App\Enums\StatusAtivo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ativo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'tipo_ativo_id',
        'modelo',
        'numero_serie',
        'foto_url',
        'status',
        'horimetro',
        'valor_diaria_referencia',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusAtivo::class,
            'horimetro' => 'decimal:2',
            'valor_diaria_referencia' => 'decimal:2',
        ];
    }

    public function tipoAtivo(): BelongsTo
    {
        return $this->belongsTo(TipoAtivo::class);
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }

    public function alertasManutencao()
    {
        return $this->hasMany(AlertaManutencao::class);
    }

    public function ordensDeServico()
    {
        return $this->hasMany(OrdemDeServico::class);
    }
}
