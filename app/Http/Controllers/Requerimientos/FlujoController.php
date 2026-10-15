<?php

namespace App\Http\Controllers\Requerimientos;

use App\Http\Controllers\Controller;
use App\Models\Requerimiento;
use App\Services\RequerimientoFlujoService;
use Illuminate\Http\Request;

class FlujoController extends Controller
{
    public function __invoke(Request $request, Requerimiento $requerimiento, string $accion, RequerimientoFlujoService $flujo)
    {
        $this->authorize($accion, $requerimiento);

        $datos = $request->validate([
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ]);

        $flujo->ejecutar($requerimiento, $accion, $request->user(), $datos['observaciones'] ?? null);

        $mensaje = match ($accion) {
            'enviar' => "{$requerimiento->codigo} se envió a revisión.",
            'tomar' => "Tomó {$requerimiento->codigo} para revisión.",
            'aprobar' => "{$requerimiento->codigo} fue aprobado.",
            'rechazar' => "{$requerimiento->codigo} fue rechazado.",
            'iniciar' => "{$requerimiento->codigo} pasó a desarrollo.",
            'finalizar' => "{$requerimiento->codigo} fue finalizado.",
        };

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $mensaje,
                'estado' => $requerimiento->estado->value,
                'gestor' => $requerimiento->gestor()->value('name'),
            ]);
        }

        return redirect()->route('requerimientos.show', $requerimiento)->with('success', $mensaje);
    }
}
