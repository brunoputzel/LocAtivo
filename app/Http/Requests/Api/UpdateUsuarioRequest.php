<?php

namespace App\Http\Requests\Api;

use App\Enums\PerfilUsuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('usuario'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($this->route('usuario'))],
            'perfil' => ['sometimes', Rule::enum(PerfilUsuario::class)],
            'cliente_id' => [
                'nullable', 'integer', 'exists:clientes,id',
                'required_if:perfil,'.PerfilUsuario::CLIENTE->value,
            ],
        ];
    }
}
