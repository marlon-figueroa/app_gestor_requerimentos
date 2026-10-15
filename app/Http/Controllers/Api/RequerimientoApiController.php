<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Requerimiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequerimientoApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Requerimiento::class);

        $requerimientos = Requerimiento::query()
            ->with(['proyecto:id,codigo,nombre', 'solicitante:id,name', 'gestor:id,name'])
            ->visiblesPara($request->user())
            ->when($request->filled('proyecto_id'), fn ($q) => $q->where('proyecto_id', (int) $request->input('proyecto_id')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->orderByDesc('id')
            ->paginate(min((int) $request->input('per_page', 25), 100));

        return response()->json($requerimientos);
    }

    public function show(Requerimiento $requerimiento): JsonResponse
    {
        $this->authorize('view', $requerimiento);

        $requerimiento->load(['proyecto', 'solicitante', 'gestor', 'casosUso', 'comentarios.usuario']);

        return response()->json($requerimiento);
    }
}
