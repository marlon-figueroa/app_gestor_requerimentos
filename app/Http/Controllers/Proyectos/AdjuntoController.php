<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proyectos\ProyectoRequest;
use App\Http\Requests\Requerimientos\AdjuntoRequest;
use App\Models\Proyecto;
use App\Models\ProyectoAdjunto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdjuntoController extends Controller
{
    public function store(AdjuntoRequest $request, Proyecto $proyecto): JsonResponse
    {
        $this->authorize('gestionarAdjuntos', $proyecto);
        $this->exigirActivo($proyecto);

        if ($proyecto->adjuntos()->count() >= ProyectoRequest::MAX_ADJUNTOS) {
            return response()->json([
                'message' => 'Se alcanzó el máximo de '.ProyectoRequest::MAX_ADJUNTOS.' archivos adjuntos.',
            ], 422);
        }

        $adjunto = ProyectoAdjunto::guardarSubida($proyecto, $request->file('archivo'), $request->user());

        return response()->json([
            'message' => 'Archivo adjuntado correctamente.',
            'id' => $adjunto->id,
        ]);
    }

    /**
     * Contenido en línea para el modal visor.
     */
    public function show(Proyecto $proyecto, ProyectoAdjunto $adjunto): StreamedResponse
    {
        $this->authorize('view', $proyecto);
        abort_unless($adjunto->existe(), 404);

        return Storage::disk(ProyectoAdjunto::DISCO)->response($adjunto->ruta, $adjunto->nombre_original, [
            'Content-Type' => $adjunto->mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, no-store',
        ]);
    }

    public function descargar(Proyecto $proyecto, ProyectoAdjunto $adjunto): StreamedResponse
    {
        $this->authorize('view', $proyecto);
        abort_unless($adjunto->existe(), 404);

        return Storage::disk(ProyectoAdjunto::DISCO)->download($adjunto->ruta, $adjunto->nombre_original, [
            'Content-Type' => $adjunto->mime,
        ]);
    }

    public function destroy(Proyecto $proyecto, ProyectoAdjunto $adjunto): JsonResponse
    {
        $this->authorize('gestionarAdjuntos', $proyecto);
        $this->exigirActivo($proyecto);

        $adjunto->delete();

        return response()->json(['message' => 'Archivo adjunto eliminado correctamente.']);
    }

    private function exigirActivo(Proyecto $proyecto): void
    {
        abort_if($proyecto->estado->estaCerrado(), 403, 'El proyecto está cerrado.');
    }
}
