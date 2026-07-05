<?php

namespace App\Http\Requests\Api;

use App\Enums\TipoOrdemServico;
use App\Models\OrdemDeServico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', OrdemDeServico::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ativo_id' => ['required', 'integer', 'exists:ativos,id'],
            'tecnico_id' => ['required', 'integer', 'exists:users,id'],
            'alerta_id' => ['nullable', 'integer', 'exists:alertas_manutencao,id'],
            'tipo' => ['required', Rule::enum(TipoOrdemServico::class)],
            'descricao' => ['nullable', 'string'],
        ];
    }
}
