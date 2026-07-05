<?php

namespace App\Models;

use App\Enums\TipoCliente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'tipo',
        'cpf_cnpj',
        'email',
        'telefone',
        'endereco',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoCliente::class,
            'ativo' => 'boolean',
        ];
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }
}
