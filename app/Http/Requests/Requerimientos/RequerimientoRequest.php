<?php

namespace App\Http\Requests\Requerimientos;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RequerimientoRequest extends FormRequest
{
    public const MAX_ADJUNTOS = 10;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:20000'],
            'fecha_solicitud' => ['required', 'date_format:d/m/Y'],
            'fecha_vencimiento' => ['required', 'date_format:d/m/Y', 'after_or_equal:fecha_solicitud'],
            'adjuntos' => ['nullable', 'array', 'max:'.self::MAX_ADJUNTOS],
            'adjuntos.*' => AdjuntoRequest::reglasArchivo(),
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo' => 'título',
            'descripcion' => 'descripción',
            'fecha_solicitud' => 'fecha de solicitud',
            'fecha_vencimiento' => 'fecha de vencimiento',
            'adjuntos' => 'archivos adjuntos',
            'adjuntos.*' => 'archivo adjunto',
        ];
    }

    /**
     * Datos listos para persistir (fechas convertidas desde dd/mm/aaaa).
     *
     * @return array<string, mixed>
     */
    public function datos(): array
    {
        return [
            'titulo' => $this->validated('titulo'),
            'descripcion' => $this->validated('descripcion'),
            'fecha_solicitud' => Carbon::createFromFormat('d/m/Y', $this->validated('fecha_solicitud'))->startOfDay(),
            'fecha_vencimiento' => Carbon::createFromFormat('d/m/Y', $this->validated('fecha_vencimiento'))->startOfDay(),
        ];
    }
}
