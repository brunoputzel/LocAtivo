<?php

namespace App\Http\Requests\Api;

use App\Models\Ativo;
use Illuminate\Foundation\Http\FormRequest;

class StoreAtivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Ativo::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'tipo_ativo_id' => ['required', 'integer', 'exists:tipo_ativos,id'],
            'modelo' => ['required', 'string', 'max:255'],
            'numero_serie' => ['required', 'string', 'max:255', 'unique:ativos,numero_serie'],
            'foto_url' => ['nullable', 'url', 'max:2048'],
            'horimetro' => ['nullable', 'numeric', 'min:0'],
            'valor_diaria_referencia' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
