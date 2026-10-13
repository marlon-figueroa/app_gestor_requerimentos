<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $permisos = $this->input('permissions', []);

        if (! is_array($permisos)) {
            $permisos = [];
        }

        $this->merge([
            'permissions' => array_values(array_filter(
                $permisos,
                fn ($permiso) => is_string($permiso) && $permiso !== ''
            )),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:125', Rule::unique('roles', 'name')->ignore($this->route('role'))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }

    public function attributes(): array
    {
        return [
            'permissions' => 'permisos',
            'permissions.*' => 'permiso',
        ];
    }
}
