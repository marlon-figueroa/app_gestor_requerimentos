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
        <label for="permissions" class="form-label">Permisos</label>
        @php($selected = old('permissions', $role->exists ? $role->permissions->pluck('name')->all() : []))
        <select id="permissions" name="permissions[]" multiple data-placeholder="Seleccione los permisos"
                class="form-select select2 @error('permissions') is-invalid @enderror @error('permissions.*') is-invalid @enderror">
            @foreach ($permissions as $modulo => $items)
                <optgroup label="{{ $modulo }}">
                    @foreach ($items as $permiso)
                        <option value="{{ $permiso }}" @selected(in_array($permiso, $selected))>{{ $permiso }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
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
