<?php

namespace App\Observers;

use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BitacoraObserver
{
    /**
     * Nombre de modulo por modelo observado.
     */
    private const MODULOS = [
        User::class => 'usuarios',
        Role::class => 'roles',
        Permission::class => 'permisos',
    ];

    /**
     * Cambios que por si solos no se registran (p. ej. remember_token al cerrar sesion).
     */
    private const IGNORADOS = ['remember_token', 'updated_at', 'email_verified_at'];

    public function __construct(private BitacoraService $bitacora) {}

    public function created(Model $model): void
    {
        if ($this->omitir()) {
            return;
        }

        $this->bitacora->registrarModelo('crear', $model, $this->modulo($model), nuevos: $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $cambios = array_diff_key($model->getChanges(), array_flip(self::IGNORADOS));

        if ($this->omitir() || $cambios === []) {
            return;
        }

        $anteriores = array_intersect_key($model->getOriginal(), $cambios);

        $this->bitacora->registrarModelo('actualizar', $model, $this->modulo($model), $anteriores, $cambios);
    }

    public function deleted(Model $model): void
    {
        if ($this->omitir()) {
            return;
        }

        $this->bitacora->registrarModelo('eliminar', $model, $this->modulo($model), anteriores: $model->getOriginal());
    }

    private function modulo(Model $model): string
    {
        return self::MODULOS[$model::class] ?? str(class_basename($model))->snake()->plural()->toString();
    }

    /**
     * Seeders y comandos artisan no generan bitacora.
     */
    private function omitir(): bool
    {
        return app()->runningInConsole() && ! app()->runningUnitTests();
    }
}
