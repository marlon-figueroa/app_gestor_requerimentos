<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ProyectoDashboardService;
use App\Services\RequerimientoDashboardService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra el panel principal.
     */
    public function index(RequerimientoDashboardService $dashboard, ProyectoDashboardService $proyectosDashboard)
    {
        $user = auth()->user();
        $graficas = $dashboard->graficas($user);
        $kpisProyectos = $user->canAny(['proyectos.ver', 'proyectos.ver_limitado'])
            ? $proyectosDashboard->kpis($user)
            : null;

        $stats = [
            [
                'label' => 'Usuarios',
                'total' => User::count(),
                'icon' => 'people',
                'color' => 'primary',
                'route' => $user->can('usuarios.ver') ? route('admin.usuarios.index') : null,
            ],
            [
                'label' => 'Roles',
                'total' => Role::count(),
                'icon' => 'person-badge',
                'color' => 'success',
                'route' => $user->can('roles.ver') ? route('admin.roles.index') : null,
            ],
            [
                'label' => 'Permisos',
                'total' => Permission::count(),
                'icon' => 'key',
                'color' => 'warning',
                'route' => $user->can('permisos.ver') ? route('admin.permisos.index') : null,
            ],
        ];

        return view('home', compact('stats', 'graficas', 'kpisProyectos'));
    }
}
