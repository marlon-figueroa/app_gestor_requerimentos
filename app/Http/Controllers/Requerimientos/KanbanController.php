<?php

namespace App\Http\Controllers\Requerimientos;

use App\Enums\EstadoRequerimiento;
use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\Requerimiento;
use App\Services\RequerimientoFlujoService;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    /**
     * Los finalizados se muestran solo si cerraron en este periodo para no saturar la columna.
     */
    private const DIAS_FINALIZADOS = 30;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Requerimiento::class);

        $requerimientos = Requerimiento::query()
            ->with(['solicitante', 'gestor', 'proyecto'])
            ->withCount(['adjuntos', 'casosUso'])
            ->visiblesPara($request->user())
            ->when($request->filled('proyecto_id'), fn ($q) => $q->where('proyecto_id', (int) $request->input('proyecto_id')))
            ->when($request->filled('prioridad'), fn ($q) => $q->where('prioridad', (string) $request->input('prioridad')))
            ->where(fn ($q) => $q
                ->where('estado', '!=', EstadoRequerimiento::Finalizado)
                ->orWhere('finalizado_at', '>=', now()->subDays(self::DIAS_FINALIZADOS)))
            ->orderBy('fecha_vencimiento')
            ->get()
            ->groupBy(fn (Requerimiento $r) => $r->estado->value);

        $transiciones = collect(EstadoRequerimiento::cases())
            ->mapWithKeys(fn (EstadoRequerimiento $origen) => [
                $origen->value => collect($origen->transicionesPermitidas())
                    ->mapWithKeys(fn (EstadoRequerimiento $destino) => [
                        $destino->value => RequerimientoFlujoService::accionEntre($origen, $destino),
                    ]),
            ]);

        return view('requerimientos.kanban', [
            'estados' => EstadoRequerimiento::cases(),
            'requerimientos' => $requerimientos,
            'transiciones' => $transiciones,
            'diasFinalizados' => self::DIAS_FINALIZADOS,
            'proyectos' => Proyecto::query()->orderByDesc('id')->get(['id', 'codigo', 'nombre'])
                ->mapWithKeys(fn (Proyecto $p) => [$p->id => "{$p->codigo} · {$p->nombre}"])
                ->all(),
            'proyectoId' => $request->integer('proyecto_id') ?: null,
            'prioridades' => \App\Enums\PrioridadRequerimiento::opciones(),
            'prioridad' => $request->input('prioridad'),
        ]);
    }
}
