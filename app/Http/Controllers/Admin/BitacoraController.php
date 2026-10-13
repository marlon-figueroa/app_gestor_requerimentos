<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\FiltraPorFechas;
use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $this->middleware('permission:bitacora.ver')->only(['data', 'show']);
        $this->middleware('permission:bitacora.exportar')->only('pdf');
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

    public function show(Bitacora $bitacora): JsonResponse
    {
        $bitacora->load('usuario:id,name,email');

        return response()->json([
            'id' => $bitacora->id,
            'fecha' => $bitacora->created_at?->format('d/m/Y H:i:s'),
            'usuario' => $bitacora->usuario ? "{$bitacora->usuario->name} ({$bitacora->usuario->email})" : 'Invitado / sistema',
            'tipo_accion' => Bitacora::etiquetaTipoAccion($bitacora->tipo_accion),
            'accion' => $bitacora->accion,
            'descripcion' => $bitacora->descripcion,
            'modulo' => $bitacora->modulo,
            'modelo' => $bitacora->modelo_type ? class_basename($bitacora->modelo_type)." #{$bitacora->modelo_id}" : null,
            'exitoso' => $bitacora->exitoso,
            'error_mensaje' => $bitacora->error_mensaje,
            'status_code' => $bitacora->status_code,
            'metodo_http' => $bitacora->metodo_http,
            'ruta' => $bitacora->ruta,
            'ip_address' => $bitacora->ip_address,
            'navegador' => $bitacora->navegador,
            'sistema_operativo' => $bitacora->sistema_operativo,
            'dispositivo' => $bitacora->dispositivo,
            'tiempo_ejecucion' => $bitacora->tiempo_ejecucion,
            'user_agent' => $bitacora->user_agent,
            'valores_anteriores' => $bitacora->valores_anteriores,
            'valores_nuevos' => $bitacora->valores_nuevos,
            'datos_adicionales' => $bitacora->datos_adicionales,
            'tags' => $bitacora->tags,
        ]);
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
