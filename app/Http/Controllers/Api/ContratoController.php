<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreContratoRequest;
use App\Models\Contrato;
use App\Services\ContratoService;
use Illuminate\Http\JsonResponse;

class ContratoController extends Controller
{
    public function __construct(private readonly ContratoService $contratoService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        return response()->json($this->contratoService->listarPara(auth()->user()));
    }

    public function show(Contrato $contrato): JsonResponse
    {
        $this->authorize('view', $contrato);

        return response()->json($contrato->load(['ativo', 'cliente']));
    }

    public function store(StoreContratoRequest $request): JsonResponse
    {
        $contrato = $this->contratoService->criar($request->validated());

        return response()->json($contrato->load(['ativo', 'cliente']), 201);
    }

    /**
     * @summary Encerra o contrato, libera o ativo e gera a cobrança dos dias efetivos.
     */
    public function encerrar(Contrato $contrato): JsonResponse
    {
        $this->authorize('encerrar', $contrato);

        $contrato = $this->contratoService->encerrar($contrato);

        return response()->json($contrato);
    }
}
