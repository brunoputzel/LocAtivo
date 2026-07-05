<?php

namespace App\Http\Requests\Api;

use App\Models\Contrato;
use Illuminate\Foundation\Http\FormRequest;

class StoreContratoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Contrato::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ativo_id' => ['required', 'integer', 'exists:ativos,id'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'valor_diaria' => ['required', 'numeric', 'min:0.01'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}
