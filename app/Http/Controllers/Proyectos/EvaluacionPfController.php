<?php

namespace App\Http\Controllers\Proyectos;

use App\Enums\TipoComponentePf;
use App\Http\Controllers\Controller;
use App\Http\Requests\Proyectos\EvaluacionPfRequest;
use App\Models\Proyecto;
use App\Models\ProyectoEvaluacionPf;
use App\Services\ProyectoEvaluacionDiagnosticoService;
use App\Services\ProyectoEvaluacionSnapshotService;
use App\Services\ProyectoPuntosFuncionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluacionPfController extends Controller
{
    private function asegurarEvaluacionDelProyecto(Proyecto $proyecto, ProyectoEvaluacionPf $evaluacion_pf): void
    {
        abort_unless($evaluacion_pf->proyecto_id === $proyecto->id, 404);
    }

    public function index(Proyecto $proyecto, ProyectoEvaluacionDiagnosticoService $diagnosticoService): View
    {
        $this->authorize('viewAny', ProyectoEvaluacionPf::class);
        $this->authorize('view', $proyecto);

        $proyecto->load('requerimientos');
        $evaluaciones = $proyecto->evaluacionesPf()->with('creador')->get();
        $diagnosticos = $evaluaciones->mapWithKeys(
            fn (ProyectoEvaluacionPf $evaluacion) => [
                $evaluacion->id => $evaluacion->diagnostico_snapshot
                    ?? $diagnosticoService->diagnosticar($proyecto, $evaluacion),
            ],
        );

        return view('proyectos.evaluaciones-pf.index', compact('proyecto', 'evaluaciones', 'diagnosticos'));
    }

    public function create(Proyecto $proyecto, ProyectoPuntosFuncionService $servicio): View
    {
        $this->authorize('create', [ProyectoEvaluacionPf::class, $proyecto]);

        $evaluacion = new ProyectoEvaluacionPf([
            'nombre' => 'Evaluación '.now()->format('d/m/Y'),
            'caracteristicas' => $servicio->caracteristicasPorDefecto(),
        ]);

        return view('proyectos.evaluaciones-pf.create', [
            'proyecto' => $proyecto,
            'evaluacion' => $evaluacion,
            'definicionPf' => $servicio->definicionCliente(),
            'tipos' => TipoComponentePf::cases(),
            'requerimientosProyecto' => $proyecto->requerimientos()->orderBy('id')->get(['id', 'codigo', 'titulo']),
        ]);
    }

    public function store(
        EvaluacionPfRequest $request,
        Proyecto $proyecto,
        ProyectoPuntosFuncionService $servicio,
        ProyectoEvaluacionSnapshotService $snapshot,
    ): RedirectResponse
    {
        $this->authorize('create', [ProyectoEvaluacionPf::class, $proyecto]);

        $evaluacion = new ProyectoEvaluacionPf(['proyecto_id' => $proyecto->id]);
        $evaluacion->creado_por = $request->user()->id;

        $servicio->sincronizarEvaluacion(
            $evaluacion,
            $request->componentes(),
            $request->caracteristicas(),
            $request->datosEvaluacion(),
        );
        $snapshot->persistirDiagnostico($proyecto, $evaluacion);

        return redirect()
            ->route('proyectos.evaluaciones-pf.show', [$proyecto, $evaluacion])
            ->with('success', 'Evaluación por puntos de función registrada.');
    }

    public function show(
        Proyecto $proyecto,
        ProyectoEvaluacionPf $evaluacion_pf,
        ProyectoPuntosFuncionService $servicio,
        ProyectoEvaluacionDiagnosticoService $diagnosticoService,
    ): View {
        $this->asegurarEvaluacionDelProyecto($proyecto, $evaluacion_pf);
        $this->authorize('view', $evaluacion_pf);

        $evaluacion_pf->load(['componentes', 'creador', 'proyecto']);
        $proyecto->load('requerimientos');
        $resumen = $servicio->resumenPorTipo($evaluacion_pf);
        $diagnostico = $evaluacion_pf->diagnostico_snapshot
            ?? $diagnosticoService->diagnosticar($proyecto, $evaluacion_pf);
        $evaluacion = $evaluacion_pf;

        return view('proyectos.evaluaciones-pf.show', compact('proyecto', 'evaluacion', 'resumen', 'diagnostico'));
    }

    public function edit(Proyecto $proyecto, ProyectoEvaluacionPf $evaluacion_pf, ProyectoPuntosFuncionService $servicio): View
    {
        $this->asegurarEvaluacionDelProyecto($proyecto, $evaluacion_pf);
        $this->authorize('update', $evaluacion_pf);

        $evaluacion_pf->load('componentes');
        $evaluacion = $evaluacion_pf;

        return view('proyectos.evaluaciones-pf.edit', [
            'proyecto' => $proyecto,
            'evaluacion' => $evaluacion,
            'definicionPf' => $servicio->definicionCliente(),
            'tipos' => TipoComponentePf::cases(),
            'requerimientosProyecto' => $proyecto->requerimientos()->orderBy('id')->get(['id', 'codigo', 'titulo']),
        ]);
    }

    public function update(
        EvaluacionPfRequest $request,
        Proyecto $proyecto,
        ProyectoEvaluacionPf $evaluacion_pf,
        ProyectoPuntosFuncionService $servicio,
        ProyectoEvaluacionSnapshotService $snapshot,
    ): RedirectResponse
    {
        $this->asegurarEvaluacionDelProyecto($proyecto, $evaluacion_pf);
        $this->authorize('update', $evaluacion_pf);

        $servicio->sincronizarEvaluacion(
            $evaluacion_pf,
            $request->componentes(),
            $request->caracteristicas(),
            $request->datosEvaluacion(),
        );
        $snapshot->persistirDiagnostico($proyecto, $evaluacion_pf);

        return redirect()
            ->route('proyectos.evaluaciones-pf.show', [$proyecto, $evaluacion_pf])
            ->with('success', 'Evaluación actualizada.');
    }

    public function duplicate(
        Request $request,
        Proyecto $proyecto,
        ProyectoEvaluacionPf $evaluacion_pf,
        ProyectoEvaluacionSnapshotService $snapshot,
    ): RedirectResponse {
        $this->asegurarEvaluacionDelProyecto($proyecto, $evaluacion_pf);
        $this->authorize('view', $evaluacion_pf);
        $this->authorize('create', [ProyectoEvaluacionPf::class, $proyecto]);

        $copia = $snapshot->duplicar($evaluacion_pf, $request->user()->id);
        $snapshot->persistirDiagnostico($proyecto, $copia);

        return redirect()
            ->route('proyectos.evaluaciones-pf.edit', [$proyecto, $copia])
            ->with('success', 'Evaluación duplicada como borrador.');
    }

    public function baseline(
        Proyecto $proyecto,
        ProyectoEvaluacionPf $evaluacion_pf,
        ProyectoEvaluacionSnapshotService $snapshot,
    ): RedirectResponse {
        $this->asegurarEvaluacionDelProyecto($proyecto, $evaluacion_pf);
        $this->authorize('update', $evaluacion_pf);

        $snapshot->marcarBaseline($proyecto, $evaluacion_pf);

        return redirect()
            ->route('proyectos.evaluaciones-pf.show', [$proyecto, $evaluacion_pf])
            ->with('success', 'Evaluación marcada como baseline aprobada.');
    }

    public function destroy(Request $request, Proyecto $proyecto, ProyectoEvaluacionPf $evaluacion_pf): RedirectResponse
    {
        $this->asegurarEvaluacionDelProyecto($proyecto, $evaluacion_pf);
        $this->authorize('delete', $evaluacion_pf);

        $evaluacion_pf->delete();

        return redirect()
            ->route('proyectos.evaluaciones-pf.index', $proyecto)
            ->with('success', 'Evaluación eliminada.');
    }

    public function pdf(
        Proyecto $proyecto,
        ProyectoEvaluacionPf $evaluacion_pf,
        ProyectoPuntosFuncionService $servicio,
        ProyectoEvaluacionDiagnosticoService $diagnosticoService,
    ) {
        $this->asegurarEvaluacionDelProyecto($proyecto, $evaluacion_pf);
        $this->authorize('export', $evaluacion_pf);

        $evaluacion_pf->load(['componentes', 'creador', 'proyecto.responsable']);
        $proyecto->load('requerimientos');
        $resumen = $servicio->resumenPorTipo($evaluacion_pf);
        $diagnostico = $diagnosticoService->diagnosticar($proyecto, $evaluacion_pf);
        $evaluacion = $evaluacion_pf;

        return Pdf::loadView('pdf.proyecto-evaluacion-pf', compact('proyecto', 'evaluacion', 'resumen', 'diagnostico'))
            ->setPaper('letter', 'portrait')
            ->stream($proyecto->codigo.'_pf_'.$evaluacion_pf->id.'_'.now()->format('Ymd_His').'.pdf');
    }
}
