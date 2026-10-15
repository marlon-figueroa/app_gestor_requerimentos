<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\FiltraPorFechas;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use App\Services\BitacoraService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    use FiltraPorFechas;

    public function __construct()
    {
        $this->middleware('permission:usuarios.ver')->only(['index', 'data']);
        $this->middleware('permission:usuarios.crear')->only(['create', 'store']);
        $this->middleware('permission:usuarios.editar')->only(['edit', 'update']);
        $this->middleware('permission:usuarios.eliminar')->only('destroy');
        $this->middleware('permission:usuarios.exportar')->only('pdf');
    }

    public function index()
    {
        $roles = Role::orderBy('name')->pluck('name');

        return view('admin.usuarios.index', compact('roles'));
    }

    public function data(Request $request): JsonResponse
    {
        return DataTables::eloquent($this->filteredQuery($request))
            ->addColumn('roles', fn (User $user) => view('admin.partials.badges', [
                'items' => $user->roles->pluck('name'),
                'color' => 'info',
            ])->render())
            ->editColumn('name', fn (User $user) => view('admin.usuarios.partials.nombre', ['user' => $user])->render())
            ->editColumn('created_at', fn (User $user) => $user->created_at?->format('d/m/Y H:i'))
            ->addColumn('acciones', fn (User $user) => view('admin.partials.acciones', [
                'editUrl' => route('admin.usuarios.edit', $user),
                'deleteUrl' => auth()->id() !== $user->id ? route('admin.usuarios.destroy', $user) : null,
                'editPermission' => 'usuarios.editar',
                'deletePermission' => 'usuarios.eliminar',
                'confirm' => "¿Eliminar al usuario {$user->name}?",
                'impersonateUser' => $user,
            ])->render())
            ->rawColumns(['name', 'roles', 'acciones'])
            ->toJson();
    }

    public function create()
    {
        return view('admin.usuarios.create', [
            'user' => new User,
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(UserRequest $request, BitacoraService $bitacora)
    {
        $ruta = $this->guardarFoto($request);
        $data = $request->safe()->only(['name', 'email', 'password']);

        if ($ruta) {
            $data['path_photo'] = $ruta;
        }

        try {
            $user = User::create($data);
        } catch (Throwable $e) {
            User::borrarFoto($ruta);
            throw $e;
        }

        $user->syncRoles($request->input('roles', []));
        $bitacora->registrarCambioRelacion($user, 'usuarios', 'roles', [], $user->getRoleNames()->all());

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        return view('admin.usuarios.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(UserRequest $request, User $user, BitacoraService $bitacora)
    {
        $data = $request->safe()->only(['name', 'email']);
        $fotoAnterior = $user->path_photo;
        $fotoNueva = $this->guardarFoto($request);

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        if ($fotoNueva) {
            $data['path_photo'] = $fotoNueva;
        }

        $rolesAnteriores = $user->getRoleNames()->all();

        try {
            $user->update($data);
        } catch (Throwable $e) {
            User::borrarFoto($fotoNueva);
            throw $e;
        }

        if ($fotoNueva && $fotoAnterior !== $fotoNueva) {
            User::borrarFoto($fotoAnterior);
        }
        $user->syncRoles($request->input('roles', []));
        $bitacora->registrarCambioRelacion($user, 'usuarios', 'roles', $rolesAnteriores, $user->getRoleNames()->all());

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): JsonResponse
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'No puede eliminar su propio usuario.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente.']);
    }

    public function pdf(Request $request)
    {
        $users = $this->filteredQuery($request)
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $term = '%'.$request->input('search').'%';
                $query->where(fn (Builder $q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->orderBy('name')
            ->get();

        $filtros = array_filter([
            'Rol' => $request->input('rol'),
            'Alta desde' => $request->input('fecha_desde'),
            'Alta hasta' => $request->input('fecha_hasta'),
            'Búsqueda' => $request->input('search'),
        ]);

        return Pdf::loadView('pdf.usuarios', compact('users', 'filtros'))
            ->setPaper('letter', 'landscape')
            ->stream('usuarios_'.now()->format('Ymd_His').'.pdf');
    }

    private function guardarFoto(UserRequest $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        return User::guardarFotoSubida($request->file('photo'));
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = User::query()
            ->with('roles')
            ->select('users.*')
            ->when($request->filled('rol'), fn (Builder $q) => $q->role($request->input('rol')));

        return $this->filtrarPorFechas($query, $request, 'users.created_at');
    }
}
