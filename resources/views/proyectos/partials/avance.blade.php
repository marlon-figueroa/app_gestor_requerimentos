@php
    $total = $proyecto->requerimientos_count;
    $finalizados = $total - $proyecto->requerimientos_pendientes_count;
@endphp
@if ($total === 0)
    <span class="text-body-secondary">Sin requerimientos</span>
@else
    <span title="{{ $finalizados }} de {{ $total }} finalizados">{{ $finalizados }}/{{ $total }} finalizados</span>
@endif
