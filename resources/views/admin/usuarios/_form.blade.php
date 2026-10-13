@csrf

<div class="row g-3">
    <div class="col-12">
        <label for="photo" class="form-label">Foto</label>
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $user->photoUrl() }}" alt="Foto de {{ $user->name ?: 'usuario' }}" id="photo-preview"
                 class="rounded-circle border object-fit-cover" width="72" height="72">
            <div class="flex-grow-1">
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp,image/gif"
                       class="form-control @error('photo') is-invalid @enderror">
                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">
                    JPG, PNG, WEBP o GIF. Máximo 2 MB.
                    @if ($user->exists && $user->path_photo)
                        Al guardar otra imagen se reemplaza la foto actual.
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
               class="form-control @error('name') is-invalid @enderror" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
               class="form-control @error('email') is-invalid @enderror" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">
            Contraseña @unless ($user->exists)<span class="text-danger">*</span>@endunless
        </label>
        <input type="password" id="password" name="password" autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror" @unless ($user->exists) required @endunless>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if ($user->exists)
            <div class="form-text">Déjela en blanco para conservar la contraseña actual.</div>
        @endif
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="form-control">
    </div>

    <div class="col-12">
        <label for="roles" class="form-label">Roles</label>
        @php($selectedRoles = old('roles', $user->exists ? $user->getRoleNames()->all() : []))
        <select id="roles" name="roles[]" multiple data-placeholder="Seleccione uno o varios roles"
                class="form-select select2 @error('roles') is-invalid @enderror @error('roles.*') is-invalid @enderror">
            @foreach ($roles as $rol)
                <option value="{{ $rol }}" @selected(in_array($rol, $selectedRoles))>{{ $rol }}</option>
            @endforeach
        </select>
        @error('roles')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @error('roles.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('Cancel') }}
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> Guardar
    </button>
</div>

@push('scripts')
    <script>
        document.getElementById('photo')?.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            const preview = document.getElementById('photo-preview');

            if (!file || !preview) {
                return;
            }

            preview.src = URL.createObjectURL(file);
        });
    </script>
@endpush
