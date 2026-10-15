<?php

namespace App\Http\Requests\Proyectos;

use App\Enums\TipoComponentePf;
use App\Services\ProyectoPuntosFuncionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvaluacionPfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipos = array_map(fn (TipoComponentePf $t) => $t->value, TipoComponentePf::cases());

        return [
            'nombre' => ['required', 'string', 'max:120'],
            'notas' => ['nullable', 'string', 'max:5000'],
            'caracteristicas' => ['required', 'array', 'size:'.ProyectoPuntosFuncionService::CARACTERISTICAS_TOTAL],
            'caracteristicas.*' => ['required', 'integer', 'between:0,5'],
            'componentes' => ['required', 'array', 'min:1', 'max:200'],
            'componentes.*.tipo' => ['required', 'string', Rule::in($tipos)],
            'componentes.*.nombre' => ['required', 'string', 'max:200'],
            'componentes.*.descripcion' => ['nullable', 'string', 'max:2000'],
            'componentes.*.det' => ['required', 'integer', 'min:0', 'max:999'],
            'componentes.*.referencias' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre de la evaluación',
            'caracteristicas' => 'características generales',
            'caracteristicas.*' => 'grado de influencia',
            'componentes' => 'componentes funcionales',
            'componentes.*.tipo' => 'tipo',
            'componentes.*.nombre' => 'nombre del componente',
            'componentes.*.det' => 'DET',
            'componentes.*.referencias' => 'FTR/RET',
        ];
    }

    /**
     * @return array{nombre: string, notas: ?string}
     */
    public function datosEvaluacion(): array
    {
        return [
            'nombre' => $this->input('nombre'),
            'notas' => $this->input('notas'),
        ];
    }

    /**
     * @return array<int, array{tipo: string, nombre: string, descripcion: ?string, det: int, referencias: int}>
     */
    public function componentes(): array
    {
        return collect($this->input('componentes', []))
            ->map(fn (array $fila) => [
                'tipo' => $fila['tipo'],
                'nombre' => $fila['nombre'],
                'descripcion' => $fila['descripcion'] ?? null,
                'det' => (int) $fila['det'],
                'referencias' => (int) $fila['referencias'],
            ])
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function caracteristicas(): array
    {
        return array_values(array_map('intval', $this->input('caracteristicas', [])));
    }
}
