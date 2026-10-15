@php
    use App\Enums\CaracteristicaGeneralPf;
    use App\Enums\TipoComponentePf;

    $filasComponentes = old('componentes', $evaluacion->exists && $evaluacion->componentes->isNotEmpty()
        ? $evaluacion->componentes->map(fn ($c) => [
            'tipo' => $c->tipo->value,
            'nombre' => $c->nombre,
            'descripcion' => $c->descripcion,
            'det' => $c->det,
            'referencias' => $c->referencias,
        ])->values()->all()
        : [['tipo' => 'ei', 'nombre' => '', 'descripcion' => '', 'det' => 1, 'referencias' => 1]]);

    $caracteristicas = old('caracteristicas', $evaluacion->caracteristicas ?? app(\App\Services\ProyectoPuntosFuncionService::class)->caracteristicasPorDefecto());
@endphp

<x-form.input name="nombre" label="Nombre de la evaluación" :value="old('nombre', $evaluacion->nombre)" required maxlength="120" col="col-md-8" />
<x-form.textarea name="notas" label="Notas" :value="old('notas', $evaluacion->notas)" rows="2" col="col-12"
                 help="Contexto, supuestos o alcance de esta estimación." />

<div class="col-12">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <label class="form-label mb-0 fw-semibold">Componentes funcionales</label>
        <button type="button" class="btn btn-sm btn-outline-primary" id="pf-agregar-fila">
            <i class="bi bi-plus-lg"></i> Agregar fila
        </button>
    </div>
    <div class="table-responsive border rounded">
        <table class="table table-sm align-middle mb-0" id="pf-tabla-componentes">
            <thead class="table-light">
                <tr>
                    <th style="width: 18%">Tipo</th>
                    <th style="width: 28%">Nombre</th>
                    <th style="width: 8%" class="pf-col-ref">FTR</th>
                    <th style="width: 8%">DET</th>
                    <th style="width: 10%" class="text-end">PF</th>
                    <th style="width: 8%"></th>
                </tr>
            </thead>
            <tbody id="pf-cuerpo-componentes">
                @foreach ($filasComponentes as $indice => $fila)
                    <tr class="pf-fila-componente" data-indice="{{ $indice }}">
                        <td>
                            <select name="componentes[{{ $indice }}][tipo]" class="form-select form-select-sm pf-tipo" required>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo->value }}" @selected(($fila['tipo'] ?? 'ei') === $tipo->value)>{{ $tipo->abreviatura() }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" name="componentes[{{ $indice }}][nombre]" class="form-control form-control-sm"
                                   value="{{ $fila['nombre'] ?? '' }}" required maxlength="200" placeholder="Descripción breve">
                            <input type="hidden" name="componentes[{{ $indice }}][descripcion]" value="{{ $fila['descripcion'] ?? '' }}">
                        </td>
                        <td>
                            <input type="number" name="componentes[{{ $indice }}][referencias]" class="form-control form-control-sm pf-referencias"
                                   value="{{ $fila['referencias'] ?? 1 }}" min="0" max="999" required>
                        </td>
                        <td>
                            <input type="number" name="componentes[{{ $indice }}][det]" class="form-control form-control-sm pf-det"
                                   value="{{ $fila['det'] ?? 1 }}" min="0" max="999" required>
                        </td>
                        <td class="text-end pf-puntos-fila text-body-secondary">—</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger pf-quitar-fila" title="Quitar fila">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @error('componentes')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<div class="col-12 mt-3">
    <h2 class="h6 text-primary border-bottom pb-2">Características generales del sistema (0–5)</h2>
    <div class="row g-3">
        @foreach (CaracteristicaGeneralPf::ordenadas() as $caracteristica)
            <div class="col-md-6 col-lg-4">
                <label class="form-label small fw-semibold" for="caracteristica-{{ $caracteristica->value }}">{{ $caracteristica->label() }}</label>
                <select name="caracteristicas[{{ $caracteristica->value }}]" id="caracteristica-{{ $caracteristica->value }}"
                        class="form-select form-select-sm pf-caracteristica" required>
                    @for ($grado = 0; $grado <= 5; $grado++)
                        <option value="{{ $grado }}" @selected((int) ($caracteristicas[$caracteristica->value] ?? 3) === $grado)>{{ $grado }}</option>
                    @endfor
                </select>
                <div class="form-text">{{ $caracteristica->descripcion() }}</div>
            </div>
        @endforeach
    </div>
</div>

<div class="col-12">
    <div class="card bg-body-tertiary border-0">
        <div class="card-body">
            <h2 class="h6 mb-3">Resumen IFPUG</h2>
            <dl class="row mb-0 small">
                <dt class="col-sm-4">Puntos de función sin ajuste</dt>
                <dd class="col-sm-8 fw-semibold" id="pf-resumen-sin-ajuste">0</dd>
                <dt class="col-sm-4">Suma de grado de influencia</dt>
                <dd class="col-sm-8" id="pf-resumen-influencia">0</dd>
                <dt class="col-sm-4">Factor de ajuste (VAF)</dt>
                <dd class="col-sm-8" id="pf-resumen-vaf">0,65</dd>
                <dt class="col-sm-4">Puntos de función ajustados</dt>
                <dd class="col-sm-8 fs-5 fw-bold text-primary" id="pf-resumen-ajustado">0,00</dd>
            </dl>
        </div>
    </div>
</div>

<script type="application/json" id="pf-definicion-datos">@json($definicionPf)</script>

@vite('resources/js/pages/proyecto-evaluacion-pf-form.js')
