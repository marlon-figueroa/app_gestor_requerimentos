<?php

namespace App\Http\Controllers\Requerimientos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Requerimientos\AdjuntoRequest;
use App\Http\Requests\Requerimientos\RequerimientoRequest;
use App\Models\Requerimiento;
use App\Models\RequerimientoAdjunto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdjuntoController extends Controller
{
    public function store(AdjuntoRequest $request, Requerimiento $requerimiento): JsonResponse
    {
        $this->authorize('gestionarAdjuntos', $requerimiento);

        if ($requerimiento->adjuntos()->count() >= RequerimientoRequest::MAX_ADJUNTOS) {
            return response()->json([
                'message' => 'Se alcanzó el máximo de '.RequerimientoRequest::MAX_ADJUNTOS.' archivos adjuntos.',
            ], 422);
        }

        $adjunto = RequerimientoAdjunto::guardarSubida($requerimiento, $request->file('archivo'), $request->user());

        return response()->json([
            'message' => 'Archivo adjuntado correctamente.',
            'id' => $adjunto->id,
        ]);
    }

    /**
     * Contenido en línea para el modal visor.
     */
    public function show(Requerimiento $requerimiento, RequerimientoAdjunto $adjunto): StreamedResponse
    {
        $this->authorize('view', $requerimiento);
        abort_unless($adjunto->existe(), 404);

        return Storage::disk(RequerimientoAdjunto::DISCO)->response($adjunto->ruta, $adjunto->nombre_original, [
            'Content-Type' => $adjunto->mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, no-store',
        ]);
    }

    public function descargar(Requerimiento $requerimiento, RequerimientoAdjunto $adjunto): StreamedResponse
    {
        $this->authorize('view', $requerimiento);
        abort_unless($adjunto->existe(), 404);

        return Storage::disk(RequerimientoAdjunto::DISCO)->download($adjunto->ruta, $adjunto->nombre_original, [
            'Content-Type' => $adjunto->mime,
        ]);
    }

    public function destroy(Requerimiento $requerimiento, RequerimientoAdjunto $adjunto): JsonResponse
    {
        $this->authorize('gestionarAdjuntos', $requerimiento);

        $adjunto->delete();

        return response()->json(['message' => 'Archivo adjunto eliminado correctamente.']);
    }
}
