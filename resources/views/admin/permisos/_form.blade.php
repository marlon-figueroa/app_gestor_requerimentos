@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $permission->name) }}" placeholder="modulo.accion"
               class="form-control @error('name') is-invalid @enderror" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Formato <code>modulo.accion</code>, por ejemplo <code>requerimientos.ver</code>.</div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.permisos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('Cancel') }}
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> Guardar
    </button>
</div>
