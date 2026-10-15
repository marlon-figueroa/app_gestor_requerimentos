<?php

namespace App\Http\Requests\Requerimientos;

use Illuminate\Foundation\Http\FormRequest;

class AdjuntoRequest extends FormRequest
{
    public const MAX_KB = 10240;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return list<string>
     */
    public static function reglasArchivo(): array
    {
        return ['file', 'mimes:png,jpg,jpeg,pdf', 'mimetypes:image/png,image/jpeg,application/pdf', 'max:'.self::MAX_KB];
    }

    public function rules(): array
    {
        return [
            'archivo' => ['required', ...self::reglasArchivo()],
        ];
    }

    public function attributes(): array
    {
        return ['archivo' => 'archivo adjunto'];
    }
}
