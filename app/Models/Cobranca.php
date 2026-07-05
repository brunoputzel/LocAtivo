<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model
{
    use HasFactory;

    protected $fillable = [
        'contrato_id',
        'valor',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
        ];
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }
}
