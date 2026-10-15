<?php

namespace App\Policies;

use App\Enums\EstadoRequerimiento;
use App\Models\Requerimiento;
use App\Models\User;

/**
 * El rol Administrador pasa todas las comprobaciones (Gate::before); por eso las
 * reglas de estado se validan otra vez en RequerimientoFlujoService.
 */
class RequerimientoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAny(['requerimientos.ver', 'requerimientos.revisar']);
    }

    public function view(User $user, Requerimiento $requerimiento): bool
    {
        return $user->can('requerimientos.revisar')
            || ($user->can('requerimientos.ver') && $this->esSolicitante($user, $requerimiento));
    }

    public function create(User $user): bool
    {
        return $user->can('requerimientos.crear');
    }

    public function update(User $user, Requerimiento $requerimiento): bool
    {
        return $user->can('requerimientos.editar')
            && $this->esSolicitante($user, $requerimiento)
            && $requerimiento->estado->esEditablePorSolicitante();
    }

    public function delete(User $user, Requerimiento $requerimiento): bool
    {
        return $user->can('requerimientos.eliminar')
            && $this->esSolicitante($user, $requerimiento)
            && $requerimiento->estado === EstadoRequerimiento::Borrador;
    }

    public function export(User $user, Requerimiento $requerimiento): bool
    {
        return $user->can('requerimientos.exportar') && $this->view($user, $requerimiento);
    }

    public function enviar(User $user, Requerimiento $requerimiento): bool
    {
        return $this->esSolicitante($user, $requerimiento)
            && $requerimiento->estado->esEditablePorSolicitante();
    }

    public function tomar(User $user, Requerimiento $requerimiento): bool
    {
        return $user->can('requerimientos.revisar') && $requerimiento->enBandeja();
    }

    public function aprobar(User $user, Requerimiento $requerimiento): bool
    {
        return $this->puedeResolverRevision($user, $requerimiento);
    }

    public function rechazar(User $user, Requerimiento $requerimiento): bool
    {
        return $this->puedeResolverRevision($user, $requerimiento);
    }

    public function iniciar(User $user, Requerimiento $requerimiento): bool
    {
        return $this->esGestorAsignado($user, $requerimiento)
            && $requerimiento->estado === EstadoRequerimiento::Aprobado;
    }

    public function finalizar(User $user, Requerimiento $requerimiento): bool
    {
        return $this->esGestorAsignado($user, $requerimiento)
            && $requerimiento->estado === EstadoRequerimiento::EnDesarrollo;
    }

    public function liberar(User $user, Requerimiento $requerimiento): bool
    {
        return $user->can('requerimientos.revisar')
            && $requerimiento->estado === EstadoRequerimiento::EnRevision
            && $requerimiento->gestor_id !== null
            && ($requerimiento->gestor_id === $user->id || $user->hasRole('Administrador'));
    }

    public function reasignar(User $user, Requerimiento $requerimiento): bool
    {
        return $user->can('requerimientos.revisar')
            && $requerimiento->estado === EstadoRequerimiento::EnRevision
            && $requerimiento->gestor_id !== null;
    }

    public function gestionarCasosUso(User $user, Requerimiento $requerimiento): bool
    {
        return $this->update($user, $requerimiento)
            || ($user->can('requerimientos.revisar') && $requerimiento->estado->esEditablePorGestor());
    }

    public function gestionarAdjuntos(User $user, Requerimiento $requerimiento): bool
    {
        return $this->update($user, $requerimiento);
    }

    /**
     * Un gestor puede aprobar o rechazar lo que tomó o lo que sigue en la bandeja
     * (en ese caso el servicio se lo asigna).
     */
    private function puedeResolverRevision(User $user, Requerimiento $requerimiento): bool
    {
        return $user->can('requerimientos.revisar')
            && $requerimiento->estado === EstadoRequerimiento::EnRevision
            && ($requerimiento->gestor_id === null || $requerimiento->gestor_id === $user->id);
    }

    private function esGestorAsignado(User $user, Requerimiento $requerimiento): bool
    {
        return $user->can('requerimientos.revisar') && $requerimiento->gestor_id === $user->id;
    }

    private function esSolicitante(User $user, Requerimiento $requerimiento): bool
    {
        return $requerimiento->solicitante_id === $user->id;
    }
}
