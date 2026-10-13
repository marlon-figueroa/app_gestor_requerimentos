<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:125',
                'regex:/^[a-z0-9_]+\.[a-z0-9_]+$/',
                Rule::unique('permissions', 'name')->ignore($this->route('permission')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'El nombre debe tener el formato modulo.accion (por ejemplo: usuarios.ver).',
        ];
    }
}
