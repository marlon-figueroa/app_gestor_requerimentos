<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Services\ProyectoFlujoService;
use Illuminate\Http\Request;

class FlujoController extends Controller
{
    public function __invoke(Request $request, Proyecto $proyecto, string $accion, ProyectoFlujoService $flujo)
    {
        $this->authorize($accion, $proyecto);

        $datos = $request->validate([
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ]);

        $flujo->ejecutar($proyecto, $accion, $request->user(), $datos['observaciones'] ?? null);

        $mensaje = match ($accion) {
            'finalizar' => "{$proyecto->codigo} fue finalizado.",
            'cancelar' => "{$proyecto->codigo} fue cancelado.",
        };

        if ($request->expectsJson()) {
            return response()->json(['message' => $mensaje, 'estado' => $proyecto->estado->value]);
        }

        return redirect()->route('proyectos.show', $proyecto)->with('success', $mensaje);
    }
}
