<?php

namespace App\Http\Requests\Api;

use App\Enums\TipoCliente;
use App\Models\Cliente;
use App\Rules\CpfCnpjValido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Cliente::class);
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
        $tipo = TipoCliente::tryFrom((string) $this->input('tipo'));

        return [
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::enum(TipoCliente::class)],
            'cpf_cnpj' => ['required', 'string', 'unique:clientes,cpf_cnpj', new CpfCnpjValido($tipo)],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'endereco' => ['nullable', 'string', 'max:255'],
        ];
    }
}
