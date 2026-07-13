<?php

namespace App\Http\Requests\Api;

use App\Enums\StatusAtivo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAtivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ativo'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['sometimes', 'string', 'max:255'],
            'tipo_ativo_id' => ['sometimes', 'integer', 'exists:tipo_ativos,id'],
            'modelo' => ['sometimes', 'string', 'max:255'],
            'numero_serie' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('ativos', 'numero_serie')->ignore($this->route('ativo')),
            ],
            'foto_url' => ['nullable', 'url', 'max:2048'],
            'status' => ['sometimes', Rule::enum(StatusAtivo::class)],
            'horimetro' => ['sometimes', 'numeric', 'min:0'],
            'valor_diaria_referencia' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
