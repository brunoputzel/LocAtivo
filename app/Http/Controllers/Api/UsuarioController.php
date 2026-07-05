<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUsuarioRequest;
use App\Http\Requests\Api\UpdateUsuarioRequest;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Http\JsonResponse;

class UsuarioController extends Controller
{
    public function __construct(private readonly UsuarioService $usuarioService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return response()->json(User::query()->latest()->get());
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $usuario = $this->usuarioService->criar($request->validated());

        return response()->json($usuario, 201);
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): JsonResponse
    {
        $usuario = $this->usuarioService->atualizar($usuario, $request->validated());

        return response()->json($usuario);
    }

    /**
     * @summary Desativa o usuário (soft delete lógico via campo ativo) - não remove o registro.
     */
    public function destroy(User $usuario): JsonResponse
    {
        $this->authorize('delete', $usuario);

        $this->usuarioService->alternarAtivo($usuario, false, auth()->user());

        return response()->json(null, 204);
    }

    public function ativar(User $usuario): JsonResponse
    {
        $this->authorize('update', $usuario);

        $usuario = $this->usuarioService->alternarAtivo($usuario, true, auth()->user());

        return response()->json($usuario);
    }
}
