<?php

namespace App\Http\Requests\Api;

use App\Enums\TipoCliente;
use App\Rules\CpfCnpjValido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('cliente'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('cpf_cnpj')) {
            $this->merge(['cpf_cnpj' => preg_replace('/\D/', '', (string) $this->input('cpf_cnpj'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tipo = TipoCliente::tryFrom((string) $this->input('tipo')) ?? $this->route('cliente')?->tipo;

        return [
            'nome' => ['sometimes', 'string', 'max:255'],
            'tipo' => ['sometimes', Rule::enum(TipoCliente::class)],
            'cpf_cnpj' => [
                'sometimes', 'string',
                Rule::unique('clientes', 'cpf_cnpj')->ignore($this->route('cliente')),
                new CpfCnpjValido($tipo),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'endereco' => ['nullable', 'string', 'max:255'],
        ];
    }
}
