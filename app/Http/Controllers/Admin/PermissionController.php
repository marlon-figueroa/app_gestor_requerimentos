<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionRequest;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permisos.ver')->only(['index', 'data']);
        $this->middleware('permission:permisos.crear')->only(['create', 'store']);
        $this->middleware('permission:permisos.editar')->only(['edit', 'update']);
        $this->middleware('permission:permisos.eliminar')->only('destroy');
    }

    public function index()
    {
        return view('admin.permisos.index');
    }

    public function data(): JsonResponse
    {
        $query = Permission::query()->with('roles')->select('permissions.*');

        return DataTables::eloquent($query)
            ->addColumn('roles', fn (Permission $permission) => view('admin.partials.badges', [
                'items' => $permission->roles->pluck('name'),
                'color' => 'success',
            ])->render())
            ->addColumn('acciones', fn (Permission $permission) => view('admin.partials.acciones', [
                'editUrl' => route('admin.permisos.edit', $permission),
                'deleteUrl' => route('admin.permisos.destroy', $permission),
                'editPermission' => 'permisos.editar',
                'deletePermission' => 'permisos.eliminar',
                'confirm' => "¿Eliminar el permiso {$permission->name}?",
            ])->render())
            ->rawColumns(['roles', 'acciones'])
            ->toJson();
    }

    public function create()
    {
        return view('admin.permisos.create', ['permission' => new Permission]);
    }

    public function store(PermissionRequest $request)
    {
        Permission::create(['name' => $request->input('name'), 'guard_name' => 'web']);

        return redirect()->route('admin.permisos.index')->with('success', 'Permiso creado correctamente.');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permisos.edit', compact('permission'));
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        $permission->update(['name' => $request->input('name')]);

        return redirect()->route('admin.permisos.index')->with('success', 'Permiso actualizado correctamente.');
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();

        return response()->json(['message' => 'Permiso eliminado correctamente.']);
    }
}
