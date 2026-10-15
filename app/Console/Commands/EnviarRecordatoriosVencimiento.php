<?php

namespace App\Console\Commands;

use App\Enums\EstadoProyecto;
use App\Enums\EstadoRequerimiento;
use App\Models\Proyecto;
use App\Models\Requerimiento;
use App\Models\User;
use App\Notifications\ProyectoPlazoNotification;
use App\Notifications\RequerimientoFlujoNotification;
use Illuminate\Console\Command;

class EnviarRecordatoriosVencimiento extends Command
{
    protected $signature = 'recordatorios:vencimientos {--dias=3 : Días de anticipación para requerimientos}';

    protected $description = 'Envía recordatorios de vencimiento de requerimientos y proyectos activos.';

    public function handle(): int
    {
        $dias = max(1, (int) $this->option('dias'));
        $limite = today()->addDays($dias);

        Requerimiento::query()
            ->with(['solicitante', 'gestor'])
            ->where('estado', '!=', EstadoRequerimiento::Finalizado)
            ->whereDate('fecha_vencimiento', '<=', $limite)
            ->whereDate('fecha_vencimiento', '>=', today())
            ->each(function (Requerimiento $requerimiento): void {
                $mensaje = "El requerimiento vence el {$requerimiento->fecha_vencimiento->format('d/m/Y')}.";
                $requerimiento->solicitante?->notify(new RequerimientoFlujoNotification($requerimiento, 'vencimiento', $mensaje));
                $requerimiento->gestor?->notify(new RequerimientoFlujoNotification($requerimiento, 'vencimiento', $mensaje));
            });

        Proyecto::query()
            ->with('responsable')
            ->where('estado', EstadoProyecto::Activo)
            ->whereDate('fecha_finalizacion', '<=', $limite)
            ->whereDate('fecha_finalizacion', '>=', today())
            ->each(function (Proyecto $proyecto): void {
                $mensaje = "La fecha de finalización del proyecto es {$proyecto->fecha_finalizacion->format('d/m/Y')}.";
                $proyecto->responsable?->notify(new ProyectoPlazoNotification($proyecto, $mensaje));

                User::role('Gestor')->each(function (User $gestor) use ($proyecto, $mensaje): void {
                    if ($gestor->id !== $proyecto->responsable_id) {
                        $gestor->notify(new ProyectoPlazoNotification($proyecto, $mensaje));
                    }
                });
            });

        $this->info('Recordatorios enviados.');

        return self::SUCCESS;
    }
}
