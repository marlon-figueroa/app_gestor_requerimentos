<?php

namespace App\Http\Controllers\Requerimientos;

use App\Http\Controllers\Controller;
use App\Models\Requerimiento;
use App\Models\RequerimientoComentario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    public function store(Request $request, Requerimiento $requerimiento): RedirectResponse
    {
        $this->authorize('view', $requerimiento);

        $datos = $request->validate([
            'cuerpo' => ['required', 'string', 'max:5000'],
        ]);

        RequerimientoComentario::create([
            'requerimiento_id' => $requerimiento->id,
            'usuario_id' => $request->user()->id,
            'cuerpo' => trim($datos['cuerpo']),
        ]);

        return redirect()
            ->route('requerimientos.show', $requerimiento)
            ->with('success', 'Comentario publicado.');
    }
}
