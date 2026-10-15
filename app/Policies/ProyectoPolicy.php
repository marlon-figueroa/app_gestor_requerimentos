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
        return $user->canAny(['proyectos.ver', 'proyectos.ver_limitado']);
    }

    public function view(User $user, Proyecto $proyecto): bool
    {
        if ($user->can('proyectos.ver')) {
            return $this->respetaScopeResponsable($user, $proyecto);
        }

        if ($user->can('proyectos.ver_limitado')) {
            return $this->participaEnProyecto($user, $proyecto);
        }

        return false;
    }

    public function viewResumen(User $user, Proyecto $proyecto): bool
    {
        return $this->view($user, $proyecto);
    }

    public function create(User $user): bool
    {
        return $user->can('proyectos.crear');
    }

    public function update(User $user, Proyecto $proyecto): bool
    {
        return $user->can('proyectos.editar')
            && $proyecto->estado === EstadoProyecto::Activo
            && $this->respetaScopeResponsable($user, $proyecto);
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
        return $user->can('proyectos.cerrar')
            && $proyecto->estado === EstadoProyecto::Activo
            && $this->respetaScopeResponsable($user, $proyecto);
    }

    private function respetaScopeResponsable(User $user, Proyecto $proyecto): bool
    {
        if (! config('proyectos.scope_responsable') || $user->hasRole('Administrador')) {
            return true;
        }

        if (! $user->can('proyectos.crear')) {
            return true;
        }

        return $proyecto->responsable_id === $user->id
            || $proyecto->miembros()->where('user_id', $user->id)->exists();
    }

    private function participaEnProyecto(User $user, Proyecto $proyecto): bool
    {
        return $proyecto->requerimientos()->where('solicitante_id', $user->id)->exists()
            || $proyecto->miembros()->where('user_id', $user->id)->exists();
    }
}
