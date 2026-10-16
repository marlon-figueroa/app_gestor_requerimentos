@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
    @php
        $iniciales = collect(explode(' ', $usuario->name))
            ->filter()
            ->take(2)
            ->map(fn (string $p) => mb_strtoupper(mb_substr($p, 0, 1)))
            ->implode('');
    @endphp

    <div class="perfil-page">
        <article class="card perfil-hero border-0 shadow-sm rounded-4 overflow-hidden mb-3">
            <div class="perfil-hero__cover" aria-hidden="true"></div>
            <div class="card-body perfil-hero__body px-4 px-lg-5 pb-4 pb-lg-5 pt-0">
                <div class="row g-4 align-items-end">
                    <div class="col-auto">
                        <div class="perfil-avatar-wrap">
                            <img src="{{ $usuario->photoUrl() }}" alt="" class="perfil-avatar rounded-circle object-fit-cover" width="128" height="128">
                        </div>
                    </div>
                    <div class="col min-w-0">
                        <p class="text-uppercase small fw-semibold text-primary mb-1 letter-spacing">Mi perfil</p>
                        <h1 class="h3 mb-1 fw-bold text-truncate">{{ $usuario->name }}</h1>
                        <p class="text-body-secondary mb-2">
                            <i class="bi bi-envelope me-1"></i>{{ $usuario->email }}
                        </p>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            @forelse ($usuario->roles as $rol)
                                <span class="badge rounded-pill text-bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                    <i class="bi bi-shield-check me-1"></i>{{ $rol->name }}
                                </span>
                            @empty
                                <span class="badge text-bg-secondary">Sin rol asignado</span>
                            @endforelse
                            <span class="small text-body-secondary">
                                <i class="bi bi-calendar3 me-1"></i>Miembro desde {{ $usuario->created_at?->translatedFormat('F Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <p class="small text-body-secondary border-top pt-3 mt-4 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Los roles los administra un administrador. Aquí puedes actualizar tu información personal, foto y contraseña.
                </p>

                <div class="row g-3 mt-3">
                    <div class="col-6 col-lg-3">
                        <div class="perfil-stat rounded-3 h-100">
                            <div class="perfil-stat__value">{{ $estadisticas['proyectos'] }}</div>
                            <div class="perfil-stat__label">Proyectos</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="perfil-stat rounded-3 h-100">
                            <div class="perfil-stat__value">{{ $estadisticas['requerimientos'] }}</div>
                            <div class="perfil-stat__label">Requerimientos</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="perfil-stat rounded-3 h-100">
                            <div class="perfil-stat__value">{{ $estadisticas['gestionados'] }}</div>
                            <div class="perfil-stat__label">Como gestor</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="perfil-stat rounded-3 h-100">
                            <div class="perfil-stat__value">{{ $estadisticas['activos'] }}</div>
                            <div class="perfil-stat__label">En curso</div>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <nav class="nav nav-pills perfil-subnav gap-2 mb-4 flex-nowrap overflow-auto pb-1" aria-label="Secciones del perfil">
            <a class="nav-link active" href="#informacion"><i class="bi bi-person me-1"></i> Información</a>
            <a class="nav-link" href="#seguridad"><i class="bi bi-key me-1"></i> Seguridad</a>
            <a class="nav-link" href="#proyectos"><i class="bi bi-briefcase me-1"></i> Proyectos</a>
            <a class="nav-link" href="#requerimientos"><i class="bi bi-clipboard-check me-1"></i> Requerimientos</a>
        </nav>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4" id="informacion">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <h2 class="h5 mb-0">Información personal</h2>
                        <p class="small text-body-secondary mb-0 mt-1">Nombre, correo y foto visible en el sistema.</p>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            @method('PUT')

                            <div class="mb-3 position-relative">
                                <label id="photo-label" class="form-label">Foto de perfil</label>
                                <div id="photo-dropzone"
                                     class="dropzone photo-dropzone @error('photo') is-invalid @enderror"
                                     aria-labelledby="photo-label"
                                     data-existing-photo="{{ $usuario->photoUrl() }}"></div>
                                <input type="file" id="photo" name="photo" class="photo-dropzone-input" tabindex="-1"
                                       accept="image/jpeg,image/png,image/webp,image/gif" aria-labelledby="photo-label">
                                <div class="form-text">JPG, PNG, WEBP o GIF. Máximo 2 MB.</div>
                                @error('photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name', $usuario->name) }}"
                                       class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}"
                                       class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check2-circle me-1"></i> Guardar cambios
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mt-4" id="seguridad">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <h2 class="h5 mb-0">Seguridad</h2>
                        <p class="small text-body-secondary mb-0 mt-1">Actualice su contraseña de acceso.</p>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('perfil.password') }}" autocomplete="off">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Contraseña actual <span class="text-danger">*</span></label>
                                <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                                       class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva contraseña <span class="text-danger">*</span></label>
                                <input type="password" id="password" name="password" autocomplete="new-password"
                                       class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Mínimo 8 caracteres.</div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-shield-lock me-1"></i> Cambiar contraseña
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 mb-4" id="proyectos">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h2 class="h5 mb-0">Proyectos</h2>
                            <p class="small text-body-secondary mb-0">Donde es responsable, integra el equipo o tiene requerimientos.</p>
                        </div>
                        <span class="badge text-bg-primary rounded-pill">{{ $proyectos->count() }}</span>
                    </div>
                    <div class="card-body p-4">
                        @if ($proyectos->isEmpty())
                            @include('perfil.partials.vacio', [
                                'icon' => 'briefcase',
                                'titulo' => 'Sin proyectos aún',
                                'texto' => 'Cuando participe en un proyecto o cree requerimientos, aparecerán aquí.',
                            ])
                            @can('create', \App\Models\Requerimiento::class)
                                <div class="text-center pb-2">
                                    <a href="{{ route('requerimientos.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Nuevo requerimiento
                                    </a>
                                </div>
                            @endcan
                        @else
                            <div class="row g-3">
                                @foreach ($proyectos as $proyecto)
                                    <div class="col-md-6">
                                        <article class="perfil-proyecto-card h-100 rounded-3 p-3">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                <div class="min-w-0 flex-grow-1">
                                                    <div class="fw-semibold">
                                                        @can('view', $proyecto)
                                                            <a href="{{ route('proyectos.show', $proyecto) }}" class="text-decoration-none">
                                                                {{ $proyecto->codigo }}
                                                            </a>
                                                        @else
                                                            {{ $proyecto->codigo }}
                                                        @endcan
                                                    </div>
                                                    <div class="small text-body-secondary text-truncate" title="{{ $proyecto->nombre }}">{{ $proyecto->nombre }}</div>
                                                </div>
                                                @include('proyectos.partials.estado', ['estado' => $proyecto->estado])
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mb-2">
                                                @foreach ($proyecto->participacion_etiquetas as $etiqueta)
                                                    <span class="badge rounded-pill text-bg-light border">{{ $etiqueta }}</span>
                                                @endforeach
                                            </div>
                                            <ul class="list-unstyled small text-body-secondary mb-0 perfil-proyecto-card__meta">
                                                <li><i class="bi bi-person me-1"></i>{{ $proyecto->responsable?->name ?? 'Sin responsable' }}</li>
                                                <li class="mt-1">
                                                    <i class="bi bi-clipboard-check me-1"></i>
                                                    {{ $proyecto->mis_requerimientos_count }} req. suyos · {{ $proyecto->requerimientos_count }} en total
                                                </li>
                                            </ul>
                                        </article>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @if ($proyectos->isNotEmpty())
                        <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                            @can('viewAny', \App\Models\Proyecto::class)
                                @if (auth()->user()->can('proyectos.ver_limitado') && ! auth()->user()->can('proyectos.ver'))
                                    <a href="{{ route('proyectos.mis-proyectos') }}" class="btn btn-sm btn-outline-secondary">
                                        Ver mis proyectos
                                    </a>
                                @else
                                    <a href="{{ route('proyectos.index') }}" class="btn btn-sm btn-outline-secondary">
                                        Ir al listado de proyectos
                                    </a>
                                @endif
                            @endcan
                        </div>
                    @endif
                </div>

                <div class="card border-0 shadow-sm rounded-4" id="requerimientos">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h2 class="h5 mb-0">Requerimientos recientes</h2>
                            <p class="small text-body-secondary mb-0">Como solicitante o gestor, ordenados por actividad.</p>
                        </div>
                        <span class="badge text-bg-secondary rounded-pill">{{ $estadisticas['requerimientos'] }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if ($requerimientos->isEmpty())
                            <div class="p-4">
                                @include('perfil.partials.vacio', [
                                    'icon' => 'clipboard',
                                    'titulo' => 'Sin requerimientos',
                                    'texto' => 'Aún no figura como solicitante ni gestor en ningún requerimiento.',
                                ])
                                @can('create', \App\Models\Requerimiento::class)
                                    <div class="text-center">
                                        <a href="{{ route('requerimientos.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Crear requerimiento
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="small text-body-secondary border-bottom">
                                        <tr>
                                            <th scope="col" class="ps-4 py-3">Código</th>
                                            <th scope="col" class="py-3">Detalle</th>
                                            <th scope="col" class="py-3">Rol</th>
                                            <th scope="col" class="py-3">Estado</th>
                                            <th scope="col" class="pe-4 py-3">Actividad</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @foreach ($requerimientos as $requerimiento)
                                            <tr>
                                                <td class="ps-4 text-nowrap align-middle">
                                                    @can('view', $requerimiento)
                                                        <a href="{{ route('requerimientos.show', $requerimiento) }}" class="fw-medium text-decoration-none">
                                                            {{ $requerimiento->codigo }}
                                                        </a>
                                                    @else
                                                        {{ $requerimiento->codigo }}
                                                    @endcan
                                                </td>
                                                <td class="align-middle">
                                                    <span class="text-body-secondary">{{ $requerimiento->proyecto?->codigo }}</span>
                                                    <div class="text-truncate perfil-req-titulo" title="{{ $requerimiento->titulo }}">{{ $requerimiento->titulo }}</div>
                                                </td>
                                                <td class="align-middle">
                                                    @foreach ($requerimiento->roles_en_requerimiento as $rol)
                                                        <span class="badge text-bg-light border">{{ $rol }}</span>
                                                    @endforeach
                                                </td>
                                                <td class="align-middle">@include('requerimientos.partials.estado', ['estado' => $requerimiento->estado])</td>
                                                <td class="pe-4 text-nowrap text-body-secondary align-middle">
                                                    {{ $requerimiento->updated_at?->diffForHumans() }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    @if ($requerimientos->isNotEmpty())
                        <div class="card-footer bg-transparent border-top-0 px-4 py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            @if ($hayMasRequerimientos)
                                <span class="small text-body-secondary">Mostrando los {{ $limiteRequerimientos }} más recientes.</span>
                            @else
                                <span class="small text-body-secondary">Historial completo en esta vista.</span>
                            @endif
                            @if (auth()->user()->hasAnyPermission(['requerimientos.ver', 'requerimientos.revisar']))
                                <a href="{{ route('requerimientos.index') }}" class="btn btn-sm btn-outline-primary">
                                    Ver listado de requerimientos
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/usuario-form.js')
    @vite('resources/js/pages/perfil.js')
@endpush
