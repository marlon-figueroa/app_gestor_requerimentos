<?php

namespace App\Services;

use App\Enums\EstadoRequerimiento;
use App\Models\Proyecto;
use App\Models\Requerimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PerfilService
{
    /**
     * @return array{proyectos: int, solicitados: int, gestionados: int, activos: int, requerimientos: int}
     */
    public function estadisticas(User $usuario): array
    {
        $id = $usuario->id;

        return [
            'proyectos' => $this->proyectosInvolucradosQuery($id)->count(),
            'solicitados' => $usuario->requerimientosSolicitados()->count(),
            'gestionados' => $usuario->requerimientosGestionados()->count(),
            'activos' => $this->requerimientosInvolucradosQuery($id)
                ->where('estado', '!=', EstadoRequerimiento::Finalizado)
                ->count(),
            'requerimientos' => $this->requerimientosInvolucradosQuery($id)->count(),
        ];
    }

    public function limiteRequerimientosRecientes(): int
    {
        return 20;
    }

    /**
     * @return Collection<int, Proyecto>
     */
    public function proyectosInvolucrados(User $usuario): Collection
    {
        $id = $usuario->id;

        return $this->proyectosInvolucradosQuery($id)
            ->with([
                'responsable',
                'miembros' => fn ($q) => $q->where('user_id', $id),
            ])
            ->withCount([
                'requerimientos',
                'requerimientos as mis_requerimientos_count' => fn (Builder $q) => $q->where(
                    fn (Builder $q2) => $q2->where('solicitante_id', $id)->orWhere('gestor_id', $id)
                ),
            ])
            ->orderByRaw('CASE WHEN responsable_id = ? THEN 0 ELSE 1 END', [$id])
            ->orderByDesc('updated_at')
            ->get()
            ->each(function (Proyecto $proyecto) use ($id): void {
                $proyecto->setAttribute('participacion_etiquetas', $this->etiquetasParticipacionProyecto($proyecto, $id));
            });
    }

    /**
     * @return Collection<int, Requerimiento>
     */
    public function requerimientosRecientes(User $usuario, int $limite = 20): Collection
    {
        $id = $usuario->id;

        return $this->requerimientosInvolucradosQuery($id)
            ->with(['proyecto:id,codigo,nombre', 'solicitante:id,name', 'gestor:id,name'])
            ->orderByDesc('updated_at')
            ->limit($limite)
            ->get()
            ->each(function (Requerimiento $requerimiento) use ($id): void {
                $roles = [];
                if ($requerimiento->solicitante_id === $id) {
                    $roles[] = 'Solicitante';
                }
                if ($requerimiento->gestor_id === $id) {
                    $roles[] = 'Gestor';
                }
                $requerimiento->setAttribute('roles_en_requerimiento', $roles);
            });
    }

    private function requerimientosInvolucradosQuery(int $userId): Builder
    {
        return Requerimiento::query()->where(
            fn (Builder $q) => $q->where('solicitante_id', $userId)->orWhere('gestor_id', $userId)
        );
    }

    private function proyectosInvolucradosQuery(int $userId): Builder
    {
        return Proyecto::query()->where(function (Builder $q) use ($userId): void {
            $q->where('responsable_id', $userId)
                ->orWhereHas('miembros', fn (Builder $m) => $m->where('user_id', $userId))
                ->orWhereHas(
                    'requerimientos',
                    fn (Builder $r) => $r->where('solicitante_id', $userId)->orWhere('gestor_id', $userId)
                );
        });
    }

    /**
     * @return list<string>
     */
    private function etiquetasParticipacionProyecto(Proyecto $proyecto, int $userId): array
    {
        $etiquetas = [];

        if ($proyecto->responsable_id === $userId) {
            $etiquetas[] = 'Responsable';
        }

        if ($proyecto->relationLoaded('miembros') && $proyecto->miembros->isNotEmpty()) {
            $etiquetas[] = 'Equipo';
        }

        if ($etiquetas === []) {
            $etiquetas[] = 'Requerimientos';
        }

        return $etiquetas;
    }
}
