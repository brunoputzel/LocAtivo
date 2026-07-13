<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoAtivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
    ];

    public function ativos(): HasMany
    {
        return $this->hasMany(Ativo::class);
    }
}
