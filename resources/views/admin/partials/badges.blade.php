@forelse ($items as $item)
    <span class="badge text-bg-{{ $color ?? 'secondary' }} me-1">{{ $item }}</span>
@empty
    <span class="text-muted small">Sin asignar</span>
@endforelse
