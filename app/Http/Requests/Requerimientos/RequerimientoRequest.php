<?php

namespace App\Http\Requests\Requerimientos;

use App\Enums\EstadoProyecto;
use App\Enums\PrioridadRequerimiento;
use App\Models\Requerimiento;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequerimientoRequest extends FormRequest
{
    public const MAX_ADJUNTOS = 10;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $actual = $this->route('requerimiento') instanceof Requerimiento
            ? $this->route('requerimiento')->proyecto_id
            : null;

        return [
            'proyecto_id' => ['required', 'integer', Rule::exists('proyectos', 'id')->where(
                fn (Builder $q) => $q->where('estado', EstadoProyecto::Activo->value)
                    ->when($actual, fn (Builder $q) => $q->orWhere('id', $actual)),
            )],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:20000'],
            'fecha_solicitud' => ['required', 'date_format:d/m/Y'],
            'fecha_vencimiento' => ['required', 'date_format:d/m/Y', 'after_or_equal:fecha_solicitud'],
            'prioridad' => ['nullable', 'string', Rule::enum(PrioridadRequerimiento::class)],
            'adjuntos' => ['nullable', 'array', 'max:'.self::MAX_ADJUNTOS],
            'adjuntos.*' => AdjuntoRequest::reglasArchivo(),
        ];
    }

    public function attributes(): array
    {
        return [
            'proyecto_id' => 'proyecto',
            'titulo' => 'título',
            'descripcion' => 'descripción',
            'fecha_solicitud' => 'fecha de solicitud',
            'fecha_vencimiento' => 'fecha de vencimiento',
            'adjuntos' => 'archivos adjuntos',
            'adjuntos.*' => 'archivo adjunto',
        ];
    }

    public function messages(): array
    {
        return [
            'proyecto_id.exists' => 'El proyecto seleccionado no existe o ya está cerrado.',
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
            'proyecto_id' => (int) $this->validated('proyecto_id'),
            'titulo' => $this->validated('titulo'),
            'descripcion' => $this->validated('descripcion'),
            'fecha_solicitud' => Carbon::createFromFormat('d/m/Y', $this->validated('fecha_solicitud'))->startOfDay(),
            'fecha_vencimiento' => Carbon::createFromFormat('d/m/Y', $this->validated('fecha_vencimiento'))->startOfDay(),
            'prioridad' => PrioridadRequerimiento::from($this->validated('prioridad') ?? PrioridadRequerimiento::Media->value),
        ];
    }
}
