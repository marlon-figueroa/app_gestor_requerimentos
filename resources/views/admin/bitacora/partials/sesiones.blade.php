<div class="tab-pane fade @if ($activa === 'sesiones') show active @endif" id="pane-sesiones" role="tabpanel" aria-labelledby="tab-sesiones" tabindex="0">
    <div class="card">
        <div class="card-body">
            <p class="text-muted small mb-3">
                Usuarios con sesión vigente según la duración configurada ({{ config('session.lifetime') }} minutos de inactividad).
            </p>
            <table id="tabla-sesiones-activas" class="table table-hover align-middle w-100"
                   data-url="{{ route('admin.bitacora.sesiones.data') }}">
                <thead>
                    <tr>
                        <th>Última actividad</th>
                        <th>Usuario</th>
                        <th>IP</th>
                        <th>Cliente</th>
                        <th class="text-end">Detalle</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
