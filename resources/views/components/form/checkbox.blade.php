@props([
    'name',
    'label' => null,
    'value' => '1',
    'uncheckedValue' => null,
    'checked' => false,
    'id' => null,
    'switch' => false,
    'required' => false,
    'help' => null,
    'col' => 'col-md-6',
])

@php
    $clave = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= str_replace('.', '_', $clave).(str_ends_with($name, '[]') ? '_'.\Illuminate\Support\Str::slug((string) $value, '_') : '');
    $marcado = ! empty(old())
        ? in_array((string) $value, array_map('strval', (array) old($clave, [])), true)
        : (bool) $checked;
    $invalido = $errors->has($clave) || $errors->has($clave.'.*');
@endphp

@if ($col)
    <div class="{{ $col }}">
@endif
    <div @class(['form-check', 'form-switch' => $switch])>
        @if (! is_null($uncheckedValue))
            <input type="hidden" name="{{ $name }}" value="{{ $uncheckedValue }}">
        @endif
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}"
               @if ($switch) role="switch" @endif
               @checked($marcado) @required($required)
               {{ $attributes->class(['form-check-input', 'is-invalid' => $invalido]) }}>
        @if ($label)
            <label for="{{ $id }}" class="form-check-label">
                {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
            </label>
        @endif
    </div>

    @include('components.form.partials.feedback')
@if ($col)
    </div>
@endif
