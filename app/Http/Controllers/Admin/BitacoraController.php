<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\FiltraPorFechas;
use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\User;
use App\Models\UserSession;
use App\Services\BitacoraService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BitacoraController extends Controller
{
    use FiltraPorFechas;

    /**
     * Maximo de registros por PDF para no agotar memoria en DomPDF.
     */
    private const LIMITE_PDF = 1000;

    public function __construct()
    {
        // La pantalla agrupa Registros, Monitoreo (Pulse) y Logs; cada pestaña se muestra segun su permiso.
        $this->middleware('permission:bitacora.ver|monitoreo.pulse|monitoreo.logs')->only('index');
        $this->middleware('permission:bitacora.ver')->only(['data', 'show', 'sessionsData', 'showSesion']);
        $this->middleware('permission:bitacora.exportar')->only('pdf');
        $this->middleware('permission:bitacora.invalidar_sesion')->only('invalidateSession');
    }

    public function index()
    {
        return view('admin.bitacora.index', [
            'usuarios' => User::orderBy('name')->pluck('name', 'id'),
            'tipos' => Bitacora::TIPOS_ACCION,
            'modulos' => Bitacora::query()->whereNotNull('modulo')->distinct()->orderBy('modulo')->pluck('modulo'),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return DataTables::eloquent($this->filteredQuery($request))
            ->orderColumn('bitacoras.created_at', 'bitacoras.created_at $1, bitacoras.id $1')
            ->editColumn('created_at', fn (Bitacora $b) => $b->created_at?->format('d/m/Y H:i:s'))
            ->addColumn('usuario_nombre', fn (Bitacora $b) => $b->usuario?->name ?? 'Invitado / sistema')
            ->editColumn('tipo_accion', fn (Bitacora $b) => view('admin.bitacora.partials.tipo', ['tipo' => $b->tipo_accion])->render())
            ->editColumn('exitoso', fn (Bitacora $b) => view('admin.bitacora.partials.resultado', ['exitoso' => $b->exitoso])->render())
            ->addColumn('acciones', fn (Bitacora $b) => view('admin.bitacora.partials.acciones', ['bitacora' => $b])->render())
            ->rawColumns(['tipo_accion', 'exitoso', 'acciones'])
            ->toJson();
    }

    public function sessionsData(Request $request): JsonResponse
    {
        return DataTables::eloquent(
            UserSession::query()->activas()->with('user:id,name,email')
        )
            ->orderColumn('last_activity', 'last_activity $1')
            ->editColumn('last_activity', fn (UserSession $sesion) => $sesion->last_activity_at?->format('d/m/Y H:i:s'))
            ->addColumn('usuario_nombre', fn (UserSession $sesion) => $sesion->user?->name ?? '—')
            ->addColumn('cliente', fn (UserSession $sesion) => $sesion->resumenCliente() ?: '—')
            ->addColumn('acciones', fn (UserSession $sesion) => view('admin.bitacora.partials.sesion-acciones', [
                'sesion' => $sesion,
            ])->render())
            ->rawColumns(['acciones'])
            ->toJson();
    }

    public function invalidateSession(Request $request, string $sesion, BitacoraService $bitacora): JsonResponse
    {
        if ($request->session()->getId() === $sesion) {
            return response()->json([
                'message' => 'No puede invalidar la sesión que está utilizando en este navegador.',
            ], 422);
        }

        $sesionActiva = UserSession::query()->activas()->with('user:id,name,email')->find($sesion);

        if (! $sesionActiva) {
            return response()->json([
                'message' => 'La sesión no existe o ya expiró.',
            ], 404);
        }

        $usuario = $sesionActiva->user;
        $nombre = $usuario?->name ?? 'Usuario';
        $admin = $request->user();

        $bitacora->registrar('sesion_invalidada', 'Desconexión forzada por administrador', [
            'usuario_id' => $sesionActiva->user_id,
            'descripcion' => "El administrador {$admin->name} invalidó la sesión activa de {$nombre}",
            'modulo' => 'sesiones',
            'sesion_id' => $sesion,
            'datos_adicionales' => [
                'administrador_id' => $admin->id,
                'administrador' => $admin->name,
                'sesion_invalidada' => $sesion,
            ],
        ]);

        $sesionActiva->delete();

        return response()->json([
            'message' => "Sesión de {$nombre} invalidada. El usuario será enviado al login de inmediato.",
        ]);
    }

    public function showSesion(string $sesion): View
    {
        $sesionActiva = UserSession::query()->activas()->with('user:id,name,email')->find($sesion);

        if (! $sesionActiva) {
            abort(404, 'La sesión no existe o ya expiró.');
        }

        $eventos = Bitacora::query()
            ->where('sesion_id', $sesion)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return view('admin.bitacora.sesion', [
            'sesion' => $sesionActiva,
            'eventos' => $eventos,
        ]);
    }

    public function show(Bitacora $bitacora): View
    {
        $bitacora->load('usuario:id,name,email');

        return view('admin.bitacora.show', compact('bitacora'));
    }

    public function pdf(Request $request)
    {
        $registros = $this->filteredQuery($request)
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $term = '%'.$request->input('search').'%';
                $query->where(fn (Builder $q) => $q
                    ->where('accion', 'like', $term)
                    ->orWhere('descripcion', 'like', $term)
                    ->orWhere('ip_address', 'like', $term)
                    ->orWhereHas('usuario', fn (Builder $u) => $u->where('name', 'like', $term)));
            })
            ->latest('bitacoras.created_at')
            ->latest('bitacoras.id')
            ->limit(self::LIMITE_PDF)
            ->get();

        $filtros = array_filter([
            'Usuario' => $request->filled('usuario_id') ? User::find($request->input('usuario_id'))?->name : null,
            'Tipo' => $request->filled('tipo_accion') ? Bitacora::etiquetaTipoAccion($request->input('tipo_accion')) : null,
            'Módulo' => $request->input('modulo'),
            'Resultado' => match ($request->input('exitoso')) {
                '1' => 'Exitoso',
                '0' => 'Fallido',
                default => null,
            },
            'Desde' => $request->input('fecha_desde'),
            'Hasta' => $request->input('fecha_hasta'),
            'Búsqueda' => $request->input('search'),
        ]);

        return Pdf::loadView('pdf.bitacora', [
            'registros' => $registros,
            'filtros' => $filtros,
            'limite' => self::LIMITE_PDF,
        ])
            ->setPaper('letter', 'landscape')
            ->stream('bitacora_'.now()->format('Ymd_His').'.pdf');
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Bitacora::query()
            ->with('usuario:id,name')
            ->select('bitacoras.*')
            ->when($request->filled('usuario_id'), fn (Builder $q) => $q->porUsuario((int) $request->input('usuario_id')))
            ->when($request->filled('tipo_accion'), fn (Builder $q) => $q->porTipoAccion($request->input('tipo_accion')))
            ->when($request->filled('modulo'), fn (Builder $q) => $q->porModulo($request->input('modulo')))
            ->when($request->input('exitoso') === '1', fn (Builder $q) => $q->exitosas())
            ->when($request->input('exitoso') === '0', fn (Builder $q) => $q->fallidas());

        return $this->filtrarPorFechas($query, $request, 'bitacoras.created_at');
    }
}
