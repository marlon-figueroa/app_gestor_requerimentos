@php
    $disponibles = \App\Http\Requests\Requerimientos\RequerimientoRequest::MAX_ADJUNTOS - ($requerimiento->exists ? $requerimiento->adjuntos()->count() : 0);
    $maxMb = \App\Http\Requests\Requerimientos\AdjuntoRequest::MAX_KB / 1024;
@endphp

<x-form.select name="proyecto_id" label="Proyecto" :options="$proyectos" :selected="$requerimiento->proyecto_id"
               placeholder="Seleccione un proyecto activo" required col="col-12"
               help="Cada requerimiento pertenece a un único proyecto activo." />

<x-form.input name="titulo" label="Título" :value="$requerimiento->titulo" required autofocus maxlength="255" col="col-12" />

<x-form.select name="prioridad" label="Prioridad" :options="$prioridades ?? []"
               :selected="$requerimiento->prioridad?->value ?? 'media'" col="col-md-4" />

<x-form.textarea name="descripcion" label="Descripción" :value="$requerimiento->descripcion" rows="6" required
                 help="Describa la necesidad, el problema que resuelve y el resultado esperado." />

<x-form.input name="fecha_solicitud" label="Fecha de solicitud" type="date" :value="$requerimiento->fecha_solicitud" required />

<x-form.input name="fecha_vencimiento" label="Fecha de vencimiento" type="date" :value="$requerimiento->fecha_vencimiento" required
              help="Plazo límite para resolver el requerimiento." />

<div class="col-12 position-relative">
    <label id="adjuntos-label" class="form-label">Archivos adjuntos</label>
    @if ($disponibles > 0)
        <div id="adjuntos-dropzone"
             class="dropzone photo-dropzone adjuntos-dropzone @error('adjuntos') is-invalid @enderror @error('adjuntos.*') is-invalid @enderror"
             aria-labelledby="adjuntos-label"
             data-max-files="{{ $disponibles }}"
             data-max-filesize="{{ $maxMb }}"></div>
        <input type="file" id="adjuntos" name="adjuntos[]" multiple class="photo-dropzone-input" tabindex="-1"
               accept="image/png,image/jpeg,application/pdf" aria-labelledby="adjuntos-label">
        <div class="form-text">
            PNG, JPG o PDF. Máximo {{ $maxMb }} MB por archivo; puede agregar {{ $disponibles }} archivo(s) más.
            @if ($requerimiento->exists)
                Los adjuntos actuales se administran desde el detalle del requerimiento.
            @endif
        </div>
    @else
        <div class="form-text">Se alcanzó el máximo de archivos adjuntos. Elimine alguno desde el detalle para agregar otros.</div>
    @endif
    @error('adjuntos')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @foreach ($errors->get('adjuntos.*') as $mensajes)
        @foreach ($mensajes as $mensaje)
            <div class="invalid-feedback d-block">{{ $mensaje }}</div>
        @endforeach
    @endforeach
</div>

@push('scripts')
    @vite('resources/js/pages/requerimiento-form.js')
@endpush
