<?php

namespace App\Http\Controllers\Api;

use App\Enums\TipoChecklist;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreChecklistRequest;
use App\Models\Checklist;
use App\Models\Contrato;
use App\Services\ChecklistService;
use Illuminate\Http\JsonResponse;

class ChecklistController extends Controller
{
    public function __construct(private readonly ChecklistService $checklistService) {}

    public function index(Contrato $contrato): JsonResponse
    {
        $this->authorize('viewAny', Checklist::class);

        return response()->json($contrato->checklists()->with('usuario')->latest()->get());
    }

    public function store(StoreChecklistRequest $request, Contrato $contrato): JsonResponse
    {
        $dados = $request->validated();

        $checklist = $this->checklistService->registrar(
            $contrato,
            $request->user(),
            TipoChecklist::from($dados['tipo']),
            $dados['observacoes'] ?? null,
            $request->file('fotos', [])
        );

        return response()->json($checklist, 201);
    }
}
