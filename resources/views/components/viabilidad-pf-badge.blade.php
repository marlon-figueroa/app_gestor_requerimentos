@props(['viabilidad', 'short' => false])

@php
    use App\Enums\ViabilidadPf;

    assert($viabilidad instanceof ViabilidadPf);

    $label = $short ? $viabilidad->labelCorto() : $viabilidad->label();
@endphp

<span {{ $attributes->class([
    'badge rounded-pill flex-shrink-0 d-inline-flex align-items-center gap-1 px-3 py-2',
    'text-bg-success' => $viabilidad === ViabilidadPf::Alta,
    'text-bg-warning text-dark' => $viabilidad === ViabilidadPf::Media,
    'text-bg-danger' => $viabilidad === ViabilidadPf::Baja,
    'text-bg-secondary' => $viabilidad === ViabilidadPf::NoViable,
]) }}>
    <i class="bi bi-{{ $viabilidad->icono() }}" aria-hidden="true"></i>
    <span>{{ $label }}</span>
</span>
