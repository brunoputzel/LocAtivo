<?php

namespace App\Http\Requests\Api;

use App\Enums\TipoChecklist;
use App\Models\Checklist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Checklist::class);
    }

    /**
     * Fonte única das regras de validação - a UI (ChecklistForm) usa o mesmo
     * array em vez de duplicar as regras de arquivo/tipo à mão.
     *
     * @return array<string, mixed>
     */
    public static function regras(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoChecklist::class)],
            'observacoes' => ['nullable', 'string'],
            'fotos' => ['nullable', 'array', 'max:10'],
            'fotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function rules(): array
    {
        return static::regras();
    }
}
