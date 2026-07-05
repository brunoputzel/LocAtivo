<?php

namespace App\Http\Requests\Api;

use App\Enums\PerfilUsuario;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'perfil' => ['required', Rule::enum(PerfilUsuario::class)],
            'cliente_id' => [
                'nullable', 'integer', 'exists:clientes,id',
                'required_if:perfil,'.PerfilUsuario::CLIENTE->value,
            ],
        ];
    }
}
