@php
    $disponibles = \App\Http\Requests\Proyectos\ProyectoRequest::MAX_ADJUNTOS - ($proyecto->exists ? $proyecto->adjuntos()->count() : 0);
    $maxMb = \App\Http\Requests\Requerimientos\AdjuntoRequest::MAX_KB / 1024;
@endphp

<x-form.input name="nombre" label="Nombre" :value="$proyecto->nombre" required autofocus maxlength="255" col="col-12" />

<x-form.textarea name="descripcion" label="Descripción" :value="$proyecto->descripcion" rows="5" required
                 help="Objetivo y alcance del proyecto." />

<x-form.select name="responsable_id" label="Responsable de ejecución" :options="$responsables" :selected="$proyecto->responsable_id"
               placeholder="Seleccione un gestor" required help="Solo usuarios con rol Gestor." />

<x-form.input name="fecha_inicio" label="Fecha de inicio" type="date" :value="$proyecto->fecha_inicio"
              help="Opcional; referencia de planificación." />

<x-form.input name="fecha_finalizacion" label="Fecha de finalización" type="date" :value="$proyecto->fecha_finalizacion" required
              help="Fecha comprometida para cerrar el proyecto." />

@if (! empty($colaboradores))
    @php
        $miembrosSeleccionados = old('miembros', $proyecto->exists ? $proyecto->miembros->pluck('user_id')->all() : []);
    @endphp
    <x-form.select name="miembros[]" label="Equipo del proyecto" :options="$colaboradores" :selected="$miembrosSeleccionados"
                   multiple allow-clear col="col-12"
                   help="Colaboradores adicionales (opcional). El responsable no debe repetirse aquí." />
@endif

<div class="col-12 position-relative">
    <label id="adjuntos-label" class="form-label">Documentos adjuntos</label>
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
            @if ($proyecto->exists)
                Los adjuntos actuales se administran desde el detalle del proyecto.
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
    @vite('resources/js/pages/proyecto-form.js')
@endpush
