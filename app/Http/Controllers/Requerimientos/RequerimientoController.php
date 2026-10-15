<?php

namespace App\Http\Controllers\Requerimientos;

use App\Enums\EstadoRequerimiento;
use App\Http\Controllers\Concerns\FiltraPorFechas;
use App\Http\Controllers\Controller;
use App\Http\Requests\Requerimientos\RequerimientoRequest;
use App\Models\Requerimiento;
use App\Models\RequerimientoAdjunto;
use App\Services\RequerimientoDiagramaService;
use App\Services\RequerimientoFlujoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RequerimientoController extends Controller
{
    use FiltraPorFechas;

    public function index()
    {
        $this->authorize('viewAny', Requerimiento::class);

        return view('requerimientos.index', [
            'estados' => EstadoRequerimiento::opciones(),
            'bandeja' => false,
        ]);
    }

    public function bandeja()
    {
        abort_unless(auth()->user()->can('requerimientos.revisar'), 403);

        return view('requerimientos.index', [
            'estados' => EstadoRequerimiento::opciones(),
            'bandeja' => true,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Requerimiento::class);

        return DataTables::eloquent($this->filteredQuery($request))
            ->editColumn('codigo', fn (Requerimiento $r) => view('requerimientos.partials.codigo', ['requerimiento' => $r])->render())
            ->addColumn('solicitante', fn (Requerimiento $r) => e($r->solicitante?->name))
            ->addColumn('gestor', fn (Requerimiento $r) => $r->gestor ? e($r->gestor->name) : '<span class="text-body-secondary">Sin asignar</span>')
            ->editColumn('estado', fn (Requerimiento $r) => view('requerimientos.partials.estado', ['estado' => $r->estado])->render())
            ->editColumn('fecha_solicitud', fn (Requerimiento $r) => $r->fecha_solicitud?->format('d/m/Y'))
            ->editColumn('fecha_vencimiento', fn (Requerimiento $r) => view('requerimientos.partials.vencimiento', ['requerimiento' => $r])->render())
            ->addColumn('acciones', fn (Requerimiento $r) => view('requerimientos.partials.acciones', ['requerimiento' => $r])->render())
            ->rawColumns(['codigo', 'gestor', 'estado', 'fecha_vencimiento', 'acciones'])
            ->toJson();
    }

    public function create()
    {
        $this->authorize('create', Requerimiento::class);

        return view('requerimientos.create', [
            'requerimiento' => new Requerimiento(['fecha_solicitud' => today()]),
        ]);
    }

    public function store(RequerimientoRequest $request, RequerimientoFlujoService $flujo)
    {
        $this->authorize('create', Requerimiento::class);

        $usuario = $request->user();

        $requerimiento = DB::transaction(function () use ($request, $usuario, $flujo) {
            $requerimiento = Requerimiento::create([...$request->datos(), 'solicitante_id' => $usuario->id]);
            $flujo->registrarCreacion($requerimiento, $usuario);

            return $requerimiento;
        });

        $this->guardarAdjuntos($request, $requerimiento);

        return redirect()->route('requerimientos.show', $requerimiento)->with('success', 'Requerimiento creado correctamente.');
    }

    public function show(Requerimiento $requerimiento, RequerimientoDiagramaService $diagrama)
    {
        $this->authorize('view', $requerimiento);

        $requerimiento->load(['solicitante', 'gestor', 'casosUso', 'adjuntos.autor', 'revisiones.usuario']);

        $acciones = collect(RequerimientoFlujoService::accionesPosibles($requerimiento))
            ->filter(fn (string $accion) => auth()->user()->can($accion, $requerimiento))
            ->values();

        $pasos = $diagrama->pasos($requerimiento);

        return view('requerimientos.show', compact('requerimiento', 'acciones', 'pasos'));
    }

    public function edit(Requerimiento $requerimiento)
    {
        $this->authorize('update', $requerimiento);

        return view('requerimientos.edit', compact('requerimiento'));
    }

    public function update(RequerimientoRequest $request, Requerimiento $requerimiento)
    {
        $this->authorize('update', $requerimiento);

        $requerimiento->update($request->datos());
        $this->guardarAdjuntos($request, $requerimiento);

        return redirect()->route('requerimientos.show', $requerimiento)->with('success', 'Requerimiento actualizado correctamente.');
    }

    public function destroy(Requerimiento $requerimiento): JsonResponse
    {
        $this->authorize('delete', $requerimiento);

        $requerimiento->delete();

        return response()->json(['message' => 'Requerimiento eliminado correctamente.']);
    }

    public function pdf(Requerimiento $requerimiento)
    {
        $this->authorize('export', $requerimiento);

        $requerimiento->load(['solicitante', 'gestor', 'casosUso', 'adjuntos', 'revisiones.usuario']);

        return Pdf::loadView('pdf.requerimiento', compact('requerimiento'))
            ->setPaper('letter')
            ->stream($requerimiento->codigo.'_'.now()->format('Ymd_His').'.pdf');
    }

    private function guardarAdjuntos(RequerimientoRequest $request, Requerimiento $requerimiento): void
    {
        $disponibles = RequerimientoRequest::MAX_ADJUNTOS - $requerimiento->adjuntos()->count();

        foreach (array_slice($request->file('adjuntos', []), 0, max($disponibles, 0)) as $archivo) {
            RequerimientoAdjunto::guardarSubida($requerimiento, $archivo, $request->user());
        }
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Requerimiento::query()
            ->with(['solicitante', 'gestor'])
            ->select('requerimientos.*')
            ->visiblesPara($request->user())
            ->when($request->boolean('bandeja'), fn (Builder $q) => $q->bandeja())
            ->when(
                EstadoRequerimiento::tryFrom((string) $request->input('estado')),
                fn (Builder $q, EstadoRequerimiento $estado) => $q->where('requerimientos.estado', $estado),
            )
            ->when($request->boolean('vencidos'), fn (Builder $q) => $q
                ->where('requerimientos.estado', '!=', EstadoRequerimiento::Finalizado)
                ->whereDate('requerimientos.fecha_vencimiento', '<', today()));

        return $this->filtrarPorFechas($query, $request, 'requerimientos.fecha_vencimiento');
    }
}
