@csrf

@php($isAdmin = $role->name === \App\Http\Controllers\Admin\RoleController::ADMIN_ROLE)

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}"
               class="form-control @error('name') is-invalid @enderror" required @readonly($isAdmin) autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if ($isAdmin)
            <div class="form-text">El nombre del rol Administrador no se puede modificar; tiene acceso total.</div>
        @endif
    </div>

    <div class="col-12">
        @php($selected = array_values(array_filter(
            (array) old('permissions', $role->exists ? $role->permissions->pluck('name')->all() : []),
            fn ($permiso) => is_string($permiso) && $permiso !== ''
        )))
        @php($catalogo = $permissions->flatten())
        @php($otorgadosCount = $catalogo->filter(fn ($permiso) => in_array($permiso, $selected, true))->count())
        @php($disponiblesCount = $catalogo->count() - $otorgadosCount)
        @php($pilaInvalida = $errors->has('permissions') || $errors->has('permissions.*'))

        <div class="permiso-pila @if ($pilaInvalida) is-invalid @endif" data-permiso-pila>
            <p class="form-label mb-1">Permisos</p>
            <p class="form-text mt-0 mb-2">
                Elige uno o varios y pásalos con los botones. Ctrl o Cmd suma a la selección; Mayús selecciona un rango.
            </p>

            <div class="permiso-pila__layout">
                <section class="permiso-pila__panel" aria-labelledby="permisos-disponibles-titulo">
                    <div class="permiso-pila__head">
                        <span class="permiso-pila__title" id="permisos-disponibles-titulo">Disponibles</span>
                        <span class="badge text-bg-secondary" data-count="available">{{ $disponiblesCount }}</span>
                    </div>
                    <div class="permiso-pila__body">
                        <div class="permiso-pila__list" role="listbox" aria-multiselectable="true" aria-labelledby="permisos-disponibles-titulo" tabindex="0" data-side="available">
                            @foreach ($permissions as $modulo => $items)
                                @php($disponibles = $items->reject(fn ($permiso) => in_array($permiso, $selected, true)))
                                @if ($disponibles->isNotEmpty())
                                    <div class="permiso-pila__group" data-group="{{ $modulo }}" data-group-order="{{ $loop->index }}">
                                        <div class="permiso-pila__group-label">{{ $modulo }}</div>
                                        @foreach ($items as $permiso)
                                            @continue(in_array($permiso, $selected, true))
                                            <button type="button" role="option" class="permiso-pila__item" data-value="{{ $permiso }}" data-group="{{ $modulo }}" data-group-order="{{ $loop->parent->index }}" data-item-order="{{ $loop->index }}" aria-selected="false" aria-label="{{ $permiso }}" title="{{ $permiso }}" tabindex="-1">
                                                <span class="permiso-pila__accion">{{ str_replace('_', ' ', \Illuminate\Support\Str::after($permiso, '.')) }}</span>
                                                <span class="permiso-pila__codigo">{{ $permiso }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <p class="permiso-pila__empty" @if ($disponiblesCount > 0) hidden @endif>
                            No quedan permisos disponibles.
                        </p>
                    </div>
                </section>

                <div class="permiso-pila__actions" role="group" aria-label="Traspaso de permisos">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-action="grant" disabled>
                        <i class="bi bi-chevron-right d-none d-md-inline" aria-hidden="true"></i>
                        <i class="bi bi-chevron-down d-inline d-md-none" aria-hidden="true"></i>
                        Asignar
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-action="grant-all" @disabled($disponiblesCount === 0)>
                        <i class="bi bi-chevron-double-right d-none d-md-inline" aria-hidden="true"></i>
                        <i class="bi bi-chevron-double-down d-inline d-md-none" aria-hidden="true"></i>
                        Asignar todos
                    </button>
                    <hr class="permiso-pila__divider">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-action="revoke" disabled>
                        <i class="bi bi-chevron-left d-none d-md-inline" aria-hidden="true"></i>
                        <i class="bi bi-chevron-up d-inline d-md-none" aria-hidden="true"></i>
                        Quitar
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-action="revoke-all" @disabled($otorgadosCount === 0)>
                        <i class="bi bi-chevron-double-left d-none d-md-inline" aria-hidden="true"></i>
                        <i class="bi bi-chevron-double-up d-inline d-md-none" aria-hidden="true"></i>
                        Quitar todos
                    </button>
                </div>

                <section class="permiso-pila__panel" aria-labelledby="permisos-otorgados-titulo">
                    <div class="permiso-pila__head">
                        <span class="permiso-pila__title" id="permisos-otorgados-titulo">Otorgados</span>
                        <span class="badge text-bg-secondary" data-count="granted">{{ $otorgadosCount }}</span>
                    </div>
                    <div class="permiso-pila__body">
                        <div class="permiso-pila__list" role="listbox" aria-multiselectable="true" aria-labelledby="permisos-otorgados-titulo" tabindex="0" data-side="granted">
                            @foreach ($permissions as $modulo => $items)
                                @php($otorgados = $items->filter(fn ($permiso) => in_array($permiso, $selected, true)))
                                @if ($otorgados->isNotEmpty())
                                    <div class="permiso-pila__group" data-group="{{ $modulo }}" data-group-order="{{ $loop->index }}">
                                        <div class="permiso-pila__group-label">{{ $modulo }}</div>
                                        @foreach ($items as $permiso)
                                            @continue(! in_array($permiso, $selected, true))
                                            <button type="button" role="option" class="permiso-pila__item" data-value="{{ $permiso }}" data-group="{{ $modulo }}" data-group-order="{{ $loop->parent->index }}" data-item-order="{{ $loop->index }}" aria-selected="false" aria-label="{{ $permiso }}" title="{{ $permiso }}" tabindex="-1">
                                                <span class="permiso-pila__accion">{{ str_replace('_', ' ', \Illuminate\Support\Str::after($permiso, '.')) }}</span>
                                                <span class="permiso-pila__codigo">{{ $permiso }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <p class="permiso-pila__empty" @if ($otorgadosCount > 0) hidden @endif>
                            Aún no hay permisos otorgados.
                        </p>
                    </div>
                </section>
            </div>

            <select id="permissions" name="permissions[]" multiple class="permiso-pila__input" tabindex="-1" aria-hidden="true">
                @foreach ($permissions as $items)
                    @foreach ($items as $permiso)
                        @continue(! in_array($permiso, $selected, true))
                        <option value="{{ $permiso }}" selected>{{ $permiso }}</option>
                    @endforeach
                @endforeach
            </select>
        </div>

        @error('permissions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @error('permissions.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('Cancel') }}
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> Guardar
    </button>
</div>

@push('scripts')
    @vite('resources/js/pages/rol-form.js')
@endpush
