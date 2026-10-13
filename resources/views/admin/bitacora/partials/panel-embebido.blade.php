<div class="tab-pane fade @if ($activa === $clave) show active @endif" id="pane-{{ $clave }}" role="tabpanel" aria-labelledby="tab-{{ $clave }}" tabindex="0">
    <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
            <span>{{ $titulo }}</span>
            <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary ms-auto">
                <i data-feather="external-link"></i> Abrir en pestaña nueva
            </a>
        </div>
        {{-- El iframe se carga al abrir la pestaña (resources/js/pages/bitacora.js). --}}
        <iframe class="panel-embebido" title="{{ $titulo }}" data-src="{{ $urlEmbebida ?? $url }}"></iframe>
    </div>
</div>
