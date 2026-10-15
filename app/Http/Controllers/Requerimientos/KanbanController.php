<?php

namespace App\Http\Controllers\Requerimientos;

use App\Enums\EstadoRequerimiento;
use App\Http\Controllers\Controller;
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
            ->with(['solicitante', 'gestor'])
            ->withCount(['adjuntos', 'casosUso'])
            ->visiblesPara($request->user())
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
        ]);
    }
}
