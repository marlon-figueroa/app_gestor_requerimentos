@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'id' => null,
    'multiple' => false,
    'select2' => true,
    'placeholder' => null,
    'allowClear' => false,
    'required' => false,
    'help' => null,
    'col' => 'col-md-6',
])

@php
    $clave = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= str_replace('.', '_', $clave);
    $nombre = $multiple && ! str_ends_with($name, '[]') ? $name.'[]' : $name;

    $opciones = collect($options)->all();
    if (array_is_list($opciones)) {
        $opciones = array_combine($opciones, $opciones) ?: [];
    }

    $seleccion = old($clave, $selected);
    $seleccion = array_map('strval', $seleccion instanceof \Illuminate\Support\Collection ? $seleccion->all() : (array) $seleccion);
    $invalido = $errors->has($clave) || $errors->has($clave.'.*');

    $atributos = $attributes->class([
        'form-select',
        'select2' => $select2,
        'is-invalid' => $invalido,
    ]);

    if ($select2) {
        $atributos = $atributos->merge([
            'data-placeholder' => $placeholder,
            'data-allow-clear' => $allowClear ? 'true' : null,
        ]);
    }
@endphp

@if ($col)
    <div class="{{ $col }}">
@endif
    @include('components.form.partials.label')

    <select id="{{ $id }}" name="{{ $nombre }}" @if ($multiple) multiple @endif @required($required) {{ $atributos }}>
        @unless ($multiple)
            <option value="">{{ $select2 ? '' : ($placeholder ?? 'Seleccione una opción') }}</option>
        @endunless
        @foreach ($opciones as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(in_array((string) $valor, $seleccion, true))>{{ $etiqueta }}</option>
        @endforeach
    </select>

    @include('components.form.partials.feedback')
@if ($col)
    </div>
@endif
