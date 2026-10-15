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
        'bitacora' => ['ver', 'exportar', 'invalidar_sesion'],
        'monitoreo' => ['pulse', 'logs'],
        'requerimientos' => ['ver', 'crear', 'editar', 'eliminar', 'exportar', 'revisar'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = collect(self::PERMISOS)
            ->flatMap(fn (array $acciones, string $modulo) => array_map(fn ($accion) => "{$modulo}.{$accion}", $acciones))
            ->map(fn (string $nombre) => Permission::findOrCreate($nombre, 'web'));

        Role::findOrCreate('Administrador', 'web')->syncPermissions($permisos);
        Role::findOrCreate('Gestor', 'web')->syncPermissions([
            'requerimientos.ver',
            'requerimientos.exportar',
            'requerimientos.revisar',
        ]);
        Role::findOrCreate('Usuario', 'web')->syncPermissions([
            'requerimientos.ver',
            'requerimientos.crear',
            'requerimientos.editar',
            'requerimientos.eliminar',
            'requerimientos.exportar',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
