<?php

namespace App\Http\Controllers\Proyectos;

use App\Enums\EstadoProyecto;
use App\Enums\EstadoRequerimiento;
use App\Http\Controllers\Controller;
use App\Http\Requests\Proyectos\ProyectoRequest;
use App\Models\Proyecto;
use App\Models\ProyectoAdjunto;
use App\Services\ProyectoDiagramaService;
use App\Services\ProyectoEvaluacionDiagnosticoService;
use App\Services\ProyectoFlujoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProyectoController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Proyecto::class);

        return view('proyectos.index', [
            'estados' => EstadoProyecto::opciones(),
            'responsables' => ProyectoRequest::responsables()->pluck('name', 'id')->all(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Proyecto::class);

        return DataTables::eloquent($this->filteredQuery($request))
            ->editColumn('codigo', fn (Proyecto $p) => view('proyectos.partials.codigo', ['proyecto' => $p])->render())
            ->addColumn('responsable', fn (Proyecto $p) => e($p->responsable?->name))
            ->addColumn('avance', fn (Proyecto $p) => view('proyectos.partials.avance', ['proyecto' => $p])->render())
            ->editColumn('estado', fn (Proyecto $p) => view('proyectos.partials.estado', ['estado' => $p->estado])->render())
            ->editColumn('fecha_finalizacion', fn (Proyecto $p) => view('proyectos.partials.fecha-finalizacion', ['proyecto' => $p])->render())
            ->addColumn('acciones', fn (Proyecto $p) => view('proyectos.partials.acciones', ['proyecto' => $p])->render())
            ->rawColumns(['codigo', 'avance', 'estado', 'fecha_finalizacion', 'acciones'])
            ->toJson();
    }

    public function create()
    {
        $this->authorize('create', Proyecto::class);

        return view('proyectos.create', [
            'proyecto' => new Proyecto,
            'responsables' => ProyectoRequest::responsables()->pluck('name', 'id')->all(),
        ]);
    }

    public function store(ProyectoRequest $request, ProyectoFlujoService $flujo)
    {
        $this->authorize('create', Proyecto::class);

        $usuario = $request->user();

        $proyecto = DB::transaction(function () use ($request, $usuario, $flujo) {
            $proyecto = new Proyecto($request->datos());
            $proyecto->creado_por = $usuario->id;
            $proyecto->save();
            $flujo->registrarCreacion($proyecto, $usuario);

            return $proyecto;
        });

        $this->guardarAdjuntos($request, $proyecto);

        return redirect()->route('proyectos.show', $proyecto)->with('success', 'Proyecto creado y activo.');
    }

    public function show(Proyecto $proyecto, ProyectoDiagramaService $diagrama, ProyectoEvaluacionDiagnosticoService $diagnosticoPf)
    {
        $this->authorize('view', $proyecto);

        $proyecto->load(['responsable', 'creador', 'adjuntos.autor', 'revisiones.usuario', 'requerimientos.solicitante', 'requerimientos.gestor', 'evaluacionesPf.creador']);

        $acciones = collect(ProyectoFlujoService::accionesPosibles($proyecto))
            ->filter(fn (string $accion) => auth()->user()->can($accion, $proyecto))
            ->values();

        $pendientes = $proyecto->requerimientos->where('estado', '!=', EstadoRequerimiento::Finalizado)->count();

        $diagrama = $diagrama->diagrama($proyecto);

        $ultimaEvaluacionPf = $proyecto->evaluacionesPf->first();
        $diagnosticoPfUltima = $ultimaEvaluacionPf
            ? $diagnosticoPf->diagnosticar($proyecto, $ultimaEvaluacionPf)
            : null;

        return view('proyectos.show', compact('proyecto', 'acciones', 'pendientes', 'diagrama', 'ultimaEvaluacionPf', 'diagnosticoPfUltima'));
    }

    public function edit(Proyecto $proyecto)
    {
        $this->authorize('update', $proyecto);
        $this->exigirActivo($proyecto);

        return view('proyectos.edit', [
            'proyecto' => $proyecto,
            'responsables' => ProyectoRequest::responsables()->pluck('name', 'id')->all(),
        ]);
    }

    public function update(ProyectoRequest $request, Proyecto $proyecto)
    {
        $this->authorize('update', $proyecto);
        $this->exigirActivo($proyecto);

        $proyecto->update($request->datos());
        $this->guardarAdjuntos($request, $proyecto);

        return redirect()->route('proyectos.show', $proyecto)->with('success', 'Proyecto actualizado correctamente.');
    }

    /**
     * Reporte de los requerimientos asociados al proyecto.
     */
    public function pdf(Proyecto $proyecto)
    {
        $this->authorize('export', $proyecto);

        $proyecto->load(['responsable', 'requerimientos.solicitante', 'requerimientos.gestor']);

        $totales = collect(EstadoRequerimiento::cases())
            ->map(fn (EstadoRequerimiento $estado) => [
                'estado' => $estado,
                'total' => $proyecto->requerimientos->where('estado', $estado)->count(),
            ])
            ->filter(fn (array $fila) => $fila['total'] > 0)
            ->values();

        return Pdf::loadView('pdf.proyecto', compact('proyecto', 'totales'))
            ->setPaper('letter', 'landscape')
            ->stream($proyecto->codigo.'_requerimientos_'.now()->format('Ymd_His').'.pdf');
    }

    private function exigirActivo(Proyecto $proyecto): void
    {
        abort_if($proyecto->estado->estaCerrado(), 403, 'El proyecto está cerrado y ya no se puede modificar.');
    }

    private function guardarAdjuntos(ProyectoRequest $request, Proyecto $proyecto): void
    {
        $disponibles = ProyectoRequest::MAX_ADJUNTOS - $proyecto->adjuntos()->count();

        foreach (array_slice($request->file('adjuntos', []), 0, max($disponibles, 0)) as $archivo) {
            ProyectoAdjunto::guardarSubida($proyecto, $archivo, $request->user());
        }
    }

    private function filteredQuery(Request $request): Builder
    {
        return Proyecto::query()
            ->select('proyectos.*')
            ->with('responsable')
            ->withCount(['requerimientos', 'requerimientosPendientes'])
            ->when(
                EstadoProyecto::tryFrom((string) $request->input('estado')),
                fn (Builder $q, EstadoProyecto $estado) => $q->where('proyectos.estado', $estado),
            )
            ->when($request->filled('responsable_id'), fn (Builder $q) => $q->where('proyectos.responsable_id', (int) $request->input('responsable_id')));
    }
}
