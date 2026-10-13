<div class="tab-pane fade @if ($activa === 'registros') show active @endif" id="pane-registros" role="tabpanel" aria-labelledby="tab-registros" tabindex="0">
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-funnel me-1"></i> Filtros</div>
        <div class="card-body">
            <form id="filtros-bitacora" class="row g-3 align-items-end" autocomplete="off">
                <div class="col-md-4">
                    <label for="usuario_id" class="form-label">Usuario</label>
                    <select id="usuario_id" name="usuario_id" class="form-select select2" data-placeholder="Todos los usuarios" data-allow-clear="true">
                        <option value=""></option>
                        @foreach ($usuarios as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tipo_accion" class="form-label">Tipo de acción</label>
                    <select id="tipo_accion" name="tipo_accion" class="form-select select2" data-placeholder="Todos" data-allow-clear="true">
                        <option value=""></option>
                        @foreach ($tipos as $valor => $etiqueta)
                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="modulo" class="form-label">Módulo</label>
                    <select id="modulo" name="modulo" class="form-select select2" data-placeholder="Todos" data-allow-clear="true">
                        <option value=""></option>
                        @foreach ($modulos as $modulo)
                            <option value="{{ $modulo }}">{{ ucfirst($modulo) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="exitoso" class="form-label">Resultado</label>
                    <select id="exitoso" name="exitoso" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Exitoso</option>
                        <option value="0">Fallido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha_desde" class="form-label">Desde</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="text" id="fecha_desde" name="fecha_desde" class="form-control datetimepicker" placeholder="dd/mm/aaaa">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="fecha_hasta" class="form-label">Hasta</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="text" id="fecha_hasta" name="fecha_hasta" class="form-control datetimepicker" placeholder="dd/mm/aaaa">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill" title="Filtrar">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="reset" class="btn btn-outline-secondary flex-fill" title="Limpiar">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="tabla-bitacora" class="table table-hover align-middle w-100" data-url="{{ route('admin.bitacora.data') }}">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Tipo</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>IP</th>
                        <th class="text-center">Resultado</th>
                        <th class="text-end">Detalle</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
