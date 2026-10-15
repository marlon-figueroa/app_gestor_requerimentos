<?php

namespace App\Services;

use App\Enums\EstadoProyecto;
use App\Enums\EstadoRequerimiento;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProyectoDashboardService
{
    /**
     * @return array{activos: int, vencidos: int, avance_promedio: ?float}
     */
    public function kpis(?User $usuario = null): array
    {
        $query = Proyecto::query()->where('estado', EstadoProyecto::Activo);

        if ($usuario && config('proyectos.scope_responsable') && ! $usuario->hasRole('Administrador')) {
            if ($usuario->can('proyectos.ver') && ! $usuario->can('proyectos.crear')) {
                $query->where('responsable_id', $usuario->id);
            }
        }

        $activos = (clone $query)->count();

        $vencidos = (clone $query)
            ->whereDate('fecha_finalizacion', '<', today())
            ->count();

        $avance = DB::table('proyectos')
            ->join('requerimientos', 'requerimientos.proyecto_id', '=', 'proyectos.id')
            ->whereNull('requerimientos.deleted_at')
            ->where('proyectos.estado', EstadoProyecto::Activo->value)
            ->when(
                $usuario && config('proyectos.scope_responsable') && ! $usuario->hasRole('Administrador') && $usuario->can('proyectos.ver') && ! $usuario->can('proyectos.crear'),
                fn ($q) => $q->where('proyectos.responsable_id', $usuario->id),
            )
            ->selectRaw('AVG(CASE WHEN requerimientos.estado = ? THEN 1 ELSE 0 END) * 100 as pct', [EstadoRequerimiento::Finalizado->value])
            ->value('pct');

        return [
            'activos' => $activos,
            'vencidos' => $vencidos,
            'avance_promedio' => $avance !== null ? round((float) $avance, 1) : null,
        ];
    }
}
