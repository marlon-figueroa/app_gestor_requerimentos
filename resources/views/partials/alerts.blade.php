@php
    $mensajes = collect(['error', 'warning', 'success', 'info', 'status'])
        ->filter(fn ($tipo) => filled(session($tipo)))
        ->map(fn ($tipo) => ['type' => $tipo === 'status' ? 'success' : $tipo, 'message' => (string) session($tipo)])
        ->values();

    if ($errors->any()) {
        $mensajes->push([
            'type' => 'warning',
            'message' => $errors->count() === 1
                ? 'Revise el campo marcado en el formulario.'
                : "Revise los {$errors->count()} campos marcados en el formulario.",
        ]);
    }
@endphp

@if ($mensajes->isNotEmpty())
    {{-- Se muestran con SweetAlert2 (resources/js/libs/alerts.js). --}}
    <script type="application/json" id="flash-messages">@json($mensajes)</script>

    <noscript>
        @foreach ($mensajes as $mensaje)
            <div class="alert alert-{{ $mensaje['type'] === 'error' ? 'danger' : $mensaje['type'] }}" role="alert">
                {{ $mensaje['message'] }}
            </div>
        @endforeach
    </noscript>
@endif
