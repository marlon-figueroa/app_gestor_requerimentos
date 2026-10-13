<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permisos por modulo con el formato modulo.accion.
     */
    private const PERMISOS = [
        'usuarios' => ['ver', 'crear', 'editar', 'eliminar', 'exportar'],
        'roles' => ['ver', 'crear', 'editar', 'eliminar'],
        'permisos' => ['ver', 'crear', 'editar', 'eliminar'],
        'bitacora' => ['ver', 'exportar'],
        'monitoreo' => ['pulse', 'logs'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = collect(self::PERMISOS)
            ->flatMap(fn (array $acciones, string $modulo) => array_map(fn ($accion) => "{$modulo}.{$accion}", $acciones))
            ->map(fn (string $nombre) => Permission::findOrCreate($nombre, 'web'));

        Role::findOrCreate('Administrador', 'web')->syncPermissions($permisos);
        Role::findOrCreate('Usuario', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
