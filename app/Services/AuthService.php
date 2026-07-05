<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * @return array{usuario: User, token: string}
     */
    public function autenticar(string $email, string $password): array
    {
        $usuario = User::where('email', $email)->first();

        if (! $usuario || ! Hash::check($password, $usuario->password)) {
            throw new AuthenticationException('Estas credenciais não conferem com nossos registros.');
        }

        if (! $usuario->ativo) {
            throw new AuthenticationException(trans('auth.inativo'));
        }

        return [
            'usuario' => $usuario,
            'token' => $usuario->createToken('api')->plainTextToken,
        ];
    }
}
