@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'id' => null,
    'required' => false,
    'help' => null,
    'col' => 'col-md-6',
    'icon' => null,
])

@php
    $clave = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= str_replace('.', '_', $clave);
    $esFecha = in_array($type, ['date', 'datetime'], true);
    $conHora = $type === 'datetime';

    if ($value instanceof \DateTimeInterface) {
        $value = $value->format($conHora ? 'd/m/Y H:i' : 'd/m/Y');
    }

    $valor = $type === 'password' ? null : old($clave, $value);
    $icono = $icon ?? ($esFecha ? 'calendar-event' : null);
    $invalido = $errors->has($clave) || $errors->has($clave.'.*');

    $atributos = $attributes->class([
        'form-control',
        'datetimepicker' => $esFecha,
        'is-invalid' => $invalido,
    ]);

    if ($esFecha) {
        $atributos = $atributos->merge([
            'placeholder' => $conHora ? 'dd/mm/aaaa hh:mm' : 'dd/mm/aaaa',
            'data-time' => $conHora ? 'true' : null,
        ]);
    }
@endphp

@if ($col)
    <div class="{{ $col }}">
@endif
    @include('components.form.partials.label')

    @if ($icono)
        <div @class(['input-group', 'has-validation' => $invalido])>
            <span class="input-group-text"><i class="bi bi-{{ $icono }}"></i></span>
    @endif
            <input type="{{ $esFecha ? 'text' : $type }}" id="{{ $id }}" name="{{ $name }}"
                   @if (! is_null($valor)) value="{{ $valor }}" @endif
                   @required($required)
                   {{ $atributos }}>
    @if ($icono)
        </div>
    @endif

    @include('components.form.partials.feedback')
@if ($col)
    </div>
@endif
