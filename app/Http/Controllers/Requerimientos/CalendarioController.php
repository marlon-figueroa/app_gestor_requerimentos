<?php

namespace App\Http\Controllers\Requerimientos;

use App\Enums\EstadoRequerimiento;
use App\Http\Controllers\Controller;
use App\Models\Requerimiento;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CalendarioController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Requerimiento::class);

        return view('requerimientos.calendario', [
            'estados' => EstadoRequerimiento::cases(),
        ]);
    }

    /**
     * Eventos para FullCalendar: un evento por fecha de vencimiento dentro del rango visible.
     */
    public function eventos(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Requerimiento::class);

        $inicio = $this->fecha($request->input('start')) ?? now()->startOfMonth();
        $fin = $this->fecha($request->input('end')) ?? now()->endOfMonth();

        $eventos = Requerimiento::query()
            ->with('solicitante')
            ->visiblesPara($request->user())
            ->whereBetween('fecha_vencimiento', [$inicio->toDateString(), $fin->toDateString()])
            ->when(
                EstadoRequerimiento::tryFrom((string) $request->input('estado')),
                fn (Builder $q, EstadoRequerimiento $estado) => $q->where('estado', $estado),
            )
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(fn (Requerimiento $r) => [
                'id' => $r->id,
                'title' => "{$r->codigo} {$r->titulo}",
                'start' => $r->fecha_vencimiento->toDateString(),
                'allDay' => true,
                'url' => route('requerimientos.show', $r),
                'backgroundColor' => $r->estado->hex(),
                'borderColor' => $r->vencido ? '#dc3545' : $r->estado->hex(),
                'textColor' => in_array($r->estado, [EstadoRequerimiento::EnRevision, EstadoRequerimiento::Aprobado], true) ? '#212529' : '#ffffff',
                'classNames' => $r->vencido ? ['fc-evento-vencido'] : [],
                'extendedProps' => [
                    'estado' => $r->estado->label(),
                    'solicitante' => $r->solicitante?->name,
                    'solicitud' => $r->fecha_solicitud?->format('d/m/Y'),
                    'vencido' => $r->vencido,
                ],
            ]);

        return response()->json($eventos);
    }

    private function fecha(?string $valor): ?Carbon
    {
        if (blank($valor)) {
            return null;
        }

        try {
            return Carbon::parse($valor);
        } catch (Throwable) {
            return null;
        }
    }
}
