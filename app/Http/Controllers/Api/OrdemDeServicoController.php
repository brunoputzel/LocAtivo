<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FecharOrdemServicoRequest;
use App\Http\Requests\Api\StoreOrdemServicoRequest;
use App\Models\OrdemDeServico;
use App\Services\OrdemDeServicoService;
use Illuminate\Http\JsonResponse;

class OrdemDeServicoController extends Controller
{
    public function __construct(private readonly OrdemDeServicoService $ordemDeServicoService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', OrdemDeServico::class);

        return response()->json(
            OrdemDeServico::query()->with(['ativo', 'tecnico', 'alerta'])->latest()->get()
        );
    }

    public function show(OrdemDeServico $ordemServico): JsonResponse
    {
        $this->authorize('view', $ordemServico);

        return response()->json($ordemServico->load(['ativo', 'tecnico', 'alerta']));
    }

    public function store(StoreOrdemServicoRequest $request): JsonResponse
    {
        $ordemServico = $this->ordemDeServicoService->abrir($request->validated());

        return response()->json($ordemServico, 201);
    }

    /**
     * @summary Fecha a ordem de serviço, libera o ativo e resolve o alerta vinculado.
     */
    public function fechar(FecharOrdemServicoRequest $request, OrdemDeServico $ordemServico): JsonResponse
    {
        $dados = $request->validated();

        $ordemServico = $this->ordemDeServicoService->fechar(
            $ordemServico,
            (float) $dados['custo'],
            $dados['data_fechamento'] ?? null
        );

        return response()->json($ordemServico);
    }
}
