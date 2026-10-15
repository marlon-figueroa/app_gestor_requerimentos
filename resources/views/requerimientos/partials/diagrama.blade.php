<div class="card mb-4" id="diagrama">
    <div class="card-header">
        <i class="bi bi-diagram-2 me-1"></i> Diagrama de pasos
    </div>
    <div class="card-body">
        <p class="small text-body-secondary">
            Cada tarjeta es una transición. Quien toma, aprueba o continúa puede ser un gestor distinto o la misma persona.
        </p>
        <ul class="list-inline small mb-3">
            <li class="list-inline-item"><span class="diagrama-leyenda diagrama-leyenda--solicitante"></span> Solicitante</li>
            <li class="list-inline-item"><span class="diagrama-leyenda diagrama-leyenda--gestor"></span> Gestor</li>
        </ul>

        @if ($pasos === [])
            <p class="text-body-secondary mb-0">Este requerimiento aún no tiene pasos registrados.</p>
        @else
            <div id="diagrama-pasos" class="diagrama-pasos" role="img" aria-label="Diagrama de pasos del requerimiento"></div>
            <script type="application/json" id="diagrama-pasos-datos">@json($pasos)</script>
        @endif
    </div>
</div>
