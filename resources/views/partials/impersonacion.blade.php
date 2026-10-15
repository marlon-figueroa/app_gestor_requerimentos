@impersonating
    @php($impersonador = app('impersonate')->getImpersonator())
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3" role="status">
        <span>
            <i class="bi bi-person-bounding-box me-1"></i>
            Está viendo la aplicación como <strong>{{ Auth::user()->name }}</strong>
            ({{ Auth::user()->email }}).
            @if ($impersonador)
                Su cuenta es {{ $impersonador->name }}.
            @endif
        </span>
        <form method="POST" action="{{ route('impersonate.leave') }}" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning">Volver a mi cuenta</button>
        </form>
    </div>
@endImpersonating
