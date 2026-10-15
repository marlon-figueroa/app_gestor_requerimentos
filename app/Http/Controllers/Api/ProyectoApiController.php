<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProyectoApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Proyecto::class);

        $proyectos = Proyecto::query()
            ->accesiblesPara($request->user())
            ->with('responsable:id,name')
            ->withCount(['requerimientos', 'requerimientosPendientes'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->orderByDesc('id')
            ->paginate(min((int) $request->input('per_page', 25), 100));

        return response()->json($proyectos);
    }

    public function show(Proyecto $proyecto): JsonResponse
    {
        $this->authorize('view', $proyecto);

        $proyecto->load(['responsable:id,name', 'miembros.usuario:id,name']);

        return response()->json($proyecto);
    }
}
