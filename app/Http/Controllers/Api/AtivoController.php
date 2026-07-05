<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAtivoRequest;
use App\Http\Requests\Api\UpdateAtivoRequest;
use App\Models\Ativo;
use App\Services\AtivoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AtivoController extends Controller
{
    public function __construct(private readonly AtivoService $ativoService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ativo::class);

        $ativos = Ativo::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->get();

        return response()->json($ativos);
    }

    public function show(Ativo $ativo): JsonResponse
    {
        $this->authorize('view', $ativo);

        return response()->json($ativo);
    }

    public function store(StoreAtivoRequest $request): JsonResponse
    {
        $ativo = $this->ativoService->criar($request->validated());

        return response()->json($ativo, 201);
    }

    public function update(UpdateAtivoRequest $request, Ativo $ativo): JsonResponse
    {
        $ativo = $this->ativoService->atualizar($ativo, $request->validated());

        return response()->json($ativo);
    }

    public function destroy(Ativo $ativo): JsonResponse
    {
        $this->authorize('delete', $ativo);

        $this->ativoService->excluir($ativo);

        return response()->json(null, 204);
    }
}
