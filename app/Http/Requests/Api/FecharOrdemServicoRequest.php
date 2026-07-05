<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class FecharOrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fechar', $this->route('ordemServico'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'custo' => ['required', 'numeric', 'min:0'],
            'data_fechamento' => ['nullable', 'date'],
        ];
    }
}
