<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $credenciais = $request->validated();

        $resultado = $this->authService->autenticar($credenciais['email'], $credenciais['password']);

        return response()->json([
            'token' => $resultado['token'],
            'usuario' => $resultado['usuario'],
        ]);
    }
}
