<?php

namespace App\Http\Requests\Proyectos;

use App\Http\Requests\Requerimientos\AdjuntoRequest;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class ProyectoRequest extends FormRequest
{
    public const MAX_ADJUNTOS = 10;

    public const ROL_RESPONSABLE = 'Gestor';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fecha = ['required', 'date_format:d/m/Y'];

        if ($this->isMethod('post')) {
            $fecha[] = 'after_or_equal:today';
        }

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:20000'],
            'responsable_id' => ['required', 'integer', function (string $atributo, mixed $valor, Closure $fallar): void {
                if (! self::responsables()->contains('id', (int) $valor)) {
                    $fallar('El responsable debe ser un usuario con rol '.self::ROL_RESPONSABLE.'.');
                }
            }],
            'fecha_finalizacion' => $fecha,
            'adjuntos' => ['nullable', 'array', 'max:'.self::MAX_ADJUNTOS],
            'adjuntos.*' => AdjuntoRequest::reglasArchivo(),
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
            'responsable_id' => 'responsable de ejecución',
            'fecha_finalizacion' => 'fecha de finalización',
            'adjuntos' => 'archivos adjuntos',
            'adjuntos.*' => 'archivo adjunto',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_finalizacion.after_or_equal' => 'La fecha de finalización no puede ser anterior a hoy.',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    public static function responsables()
    {
        return User::role(self::ROL_RESPONSABLE)->orderBy('name')->get(['id', 'name']);
    }

    /**
     * @return array<string, mixed>
     */
    public function datos(): array
    {
        return [
            'nombre' => $this->validated('nombre'),
            'descripcion' => $this->validated('descripcion'),
            'responsable_id' => (int) $this->validated('responsable_id'),
            'fecha_finalizacion' => Carbon::createFromFormat('d/m/Y', $this->validated('fecha_finalizacion'))->startOfDay(),
        ];
    }
}
