<?php

namespace App\Policies;

use App\Enums\EstadoProyecto;
use App\Models\Proyecto;
use App\Models\User;

/**
 * El rol Administrador pasa todas las comprobaciones (Gate::before); por eso las
 * reglas de estado se validan otra vez en ProyectoFlujoService y en los controladores.
 */
class ProyectoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('proyectos.ver');
    }

    public function view(User $user, Proyecto $proyecto): bool
    {
        return $user->can('proyectos.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('proyectos.crear');
    }

    public function update(User $user, Proyecto $proyecto): bool
    {
        return $user->can('proyectos.editar') && $proyecto->estado === EstadoProyecto::Activo;
    }

    public function export(User $user, Proyecto $proyecto): bool
    {
        return $user->can('proyectos.exportar') && $this->view($user, $proyecto);
    }

    public function finalizar(User $user, Proyecto $proyecto): bool
    {
        return $this->puedeCerrar($user, $proyecto);
    }

    public function cancelar(User $user, Proyecto $proyecto): bool
    {
        return $this->puedeCerrar($user, $proyecto);
    }

    public function gestionarAdjuntos(User $user, Proyecto $proyecto): bool
    {
        return $this->update($user, $proyecto);
    }

    private function puedeCerrar(User $user, Proyecto $proyecto): bool
    {
        return $user->can('proyectos.cerrar') && $proyecto->estado === EstadoProyecto::Activo;
    }
}
