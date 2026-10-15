<?php

namespace App\Policies;

use App\Enums\EstadoProyecto;
use App\Models\Proyecto;
use App\Models\ProyectoEvaluacionPf;
use App\Models\User;

class ProyectoEvaluacionPfPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('proyectos.ver');
    }

    public function view(User $user, ProyectoEvaluacionPf $evaluacion): bool
    {
        return $user->can('proyectos.ver');
    }

    public function create(User $user, Proyecto $proyecto): bool
    {
        return $user->can('proyectos.evaluar') && $proyecto->estado === EstadoProyecto::Activo;
    }

    public function update(User $user, ProyectoEvaluacionPf $evaluacion): bool
    {
        $evaluacion->loadMissing('proyecto');

        return $user->can('proyectos.evaluar')
            && $evaluacion->proyecto->estado === EstadoProyecto::Activo;
    }

    public function delete(User $user, ProyectoEvaluacionPf $evaluacion): bool
    {
        return $this->update($user, $evaluacion);
    }

    public function export(User $user, ProyectoEvaluacionPf $evaluacion): bool
    {
        return $user->can('proyectos.exportar') && $this->view($user, $evaluacion);
    }
}
