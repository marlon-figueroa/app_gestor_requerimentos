@props([
    'name',
    'label' => null,
    'value' => null,
    'id' => null,
    'rows' => 3,
    'required' => false,
    'help' => null,
    'col' => 'col-12',
])

@php
    $clave = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= str_replace('.', '_', $clave);
    $invalido = $errors->has($clave) || $errors->has($clave.'.*');
@endphp

@if ($col)
    <div class="{{ $col }}">
@endif
    @include('components.form.partials.label')

    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
              {{ $attributes->class(['form-control', 'is-invalid' => $invalido]) }}>{{ old($clave, $value) }}</textarea>

    @include('components.form.partials.feedback')
@if ($col)
    </div>
@endif
