<?php

namespace App\Http\Requests\Requerimientos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CasoUsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $requerimiento = $this->route('requerimiento');
        $casoUso = $this->route('casoUso');

        return [
            'codigo' => [
                'required', 'string', 'max:20',
                Rule::unique('casos_uso', 'codigo')->where('requerimiento_id', $requerimiento->id)->ignore($casoUso),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'pantalla' => ['nullable', 'string', 'max:255'],
            'actor' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:10000'],
            'precondiciones' => ['nullable', 'string', 'max:10000'],
            'flujo_principal' => ['nullable', 'string', 'max:20000'],
            'postcondiciones' => ['nullable', 'string', 'max:10000'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function attributes(): array
    {
        return [
            'codigo' => 'código',
            'pantalla' => 'pantalla o parte del sistema',
            'descripcion' => 'descripción',
            'flujo_principal' => 'flujo principal',
        ];
    }
}
