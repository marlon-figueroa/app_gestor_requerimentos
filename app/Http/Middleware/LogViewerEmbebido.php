<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cuando Log Viewer se muestra dentro de la pestaña "Logs" de la bitácora (?embed=1)
 * se oculta su enlace "Volver al sistema", que cargaría el sistema dentro del iframe.
 */
class LogViewerEmbebido
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->boolean('embed')) {
            config(['log-viewer.back_to_system_url' => null]);
        }

        return $next($request);
    }
}
