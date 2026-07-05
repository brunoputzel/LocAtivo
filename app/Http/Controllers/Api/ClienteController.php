<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreClienteRequest;
use App\Http\Requests\Api\UpdateClienteRequest;
use App\Models\Cliente;
use App\Services\ClienteService;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    public function __construct(private readonly ClienteService $clienteService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Cliente::class);

        return response()->json(Cliente::all());
    }

    public function show(Cliente $cliente): JsonResponse
    {
        $this->authorize('view', $cliente);

        return response()->json($cliente);
    }

    public function store(StoreClienteRequest $request): JsonResponse
    {
        $cliente = $this->clienteService->criar($request->validated());

        return response()->json($cliente, 201);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): JsonResponse
    {
        $cliente = $this->clienteService->atualizar($cliente, $request->validated());

        return response()->json($cliente);
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        $this->authorize('delete', $cliente);

        $this->clienteService->inativar($cliente);

        return response()->json(null, 204);
    }
}
