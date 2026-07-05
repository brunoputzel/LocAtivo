<?php

namespace App\Models;

use App\Enums\TipoChecklist;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'contrato_id',
        'usuario_id',
        'tipo',
        'fotos_json',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoChecklist::class,
            'fotos_json' => 'array',
        ];
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
