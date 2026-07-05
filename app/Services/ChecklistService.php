<?php

namespace App\Services;

use App\Enums\TipoChecklist;
use App\Exceptions\BusinessException;
use App\Models\Checklist;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChecklistService
{
    public function __construct(private readonly ContratoService $contratoService) {}

    /**
     * @param  array<int, UploadedFile>  $fotos
     *
     * @throws BusinessException
     */
    public function registrar(Contrato $contrato, User $usuario, TipoChecklist $tipo, ?string $observacoes, array $fotos = []): Checklist
    {
        return DB::transaction(function () use ($contrato, $usuario, $tipo, $observacoes, $fotos) {
            if ($tipo === TipoChecklist::RETORNO && ! $contrato->checklistDeSaida()) {
                throw new BusinessException('Este contrato ainda não tem checklist de saída registrado.');
            }

            $checklist = Checklist::create([
                'contrato_id' => $contrato->id,
                'usuario_id' => $usuario->id,
                'tipo' => $tipo,
                'fotos_json' => $this->armazenarFotos($contrato, $fotos),
                'observacoes' => $observacoes,
            ]);

            if ($tipo === TipoChecklist::RETORNO) {
                $this->contratoService->encerrar($contrato);
            }

            return $checklist;
        });
    }

    /**
     * @param  array<int, UploadedFile>  $fotos
     * @return array<int, string>
     */
    private function armazenarFotos(Contrato $contrato, array $fotos): array
    {
        return collect($fotos)
            ->map(function (UploadedFile $foto) use ($contrato) {
                $caminho = $foto->store("checklists/{$contrato->id}", 's3');

                return Storage::disk('s3')->url($caminho);
            })
            ->values()
            ->all();
    }
}
