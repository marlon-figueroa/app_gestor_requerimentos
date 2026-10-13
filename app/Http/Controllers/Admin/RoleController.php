<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Services\BitacoraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public const ADMIN_ROLE = 'Administrador';

    public function __construct()
    {
        $this->middleware('permission:roles.ver')->only(['index', 'data']);
        $this->middleware('permission:roles.crear')->only(['create', 'store']);
        $this->middleware('permission:roles.editar')->only(['edit', 'update']);
        $this->middleware('permission:roles.eliminar')->only('destroy');
    }

    public function index()
    {
        return view('admin.roles.index');
    }

    public function data(): JsonResponse
    {
        $query = Role::query()->withCount(['permissions', 'users']);

        return DataTables::eloquent($query)
            ->addColumn('acciones', fn (Role $role) => view('admin.partials.acciones', [
                'editUrl' => route('admin.roles.edit', $role),
                'deleteUrl' => $role->name !== self::ADMIN_ROLE ? route('admin.roles.destroy', $role) : null,
                'editPermission' => 'roles.editar',
                'deletePermission' => 'roles.eliminar',
                'confirm' => "¿Eliminar el rol {$role->name}?",
            ])->render())
            ->rawColumns(['acciones'])
            ->toJson();
    }

    public function create()
    {
        return view('admin.roles.create', [
            'role' => new Role,
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function store(RoleRequest $request, BitacoraService $bitacora)
    {
        $role = Role::create(['name' => $request->input('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));
        $bitacora->registrarCambioRelacion($role, 'roles', 'permisos', [], $role->permissions()->pluck('name')->all());

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function update(RoleRequest $request, Role $role, BitacoraService $bitacora)
    {
        $permisosAnteriores = $role->permissions()->pluck('name')->all();

        if ($role->name !== self::ADMIN_ROLE) {
            $role->update(['name' => $request->input('name')]);
        }

        $role->syncPermissions($request->input('permissions', []));
        $bitacora->registrarCambioRelacion($role, 'roles', 'permisos', $permisosAnteriores, $role->permissions()->pluck('name')->all());

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->name === self::ADMIN_ROLE) {
            return response()->json(['message' => 'El rol Administrador no se puede eliminar.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Rol eliminado correctamente.']);
    }

    /**
     * Permisos agrupados por modulo (prefijo antes del punto) para las pilas del formulario.
     */
    private function groupedPermissions(): Collection
    {
        return Permission::orderBy('name')
            ->pluck('name')
            ->groupBy(fn (string $name) => ucfirst(strtok($name, '.')));
    }
}
