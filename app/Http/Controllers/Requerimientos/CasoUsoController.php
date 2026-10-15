<?php

namespace App\Http\Controllers\Requerimientos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Requerimientos\CasoUsoRequest;
use App\Models\CasoUso;
use App\Models\Requerimiento;
use Illuminate\Http\JsonResponse;

class CasoUsoController extends Controller
{
    public function create(Requerimiento $requerimiento)
    {
        $this->authorize('gestionarCasosUso', $requerimiento);

        $casoUso = new CasoUso([
            'codigo' => $requerimiento->siguienteCodigoCasoUso(),
            'orden' => $requerimiento->casosUso()->count() + 1,
        ]);

        return view('requerimientos.casos-uso.create', compact('requerimiento', 'casoUso'));
    }

    public function store(CasoUsoRequest $request, Requerimiento $requerimiento)
    {
        $this->authorize('gestionarCasosUso', $requerimiento);

        $requerimiento->casosUso()->create($request->validated());

        return redirect()->to(route('requerimientos.show', $requerimiento).'#casos-uso')
            ->with('success', 'Caso de uso agregado correctamente.');
    }

    public function edit(Requerimiento $requerimiento, CasoUso $casoUso)
    {
        $this->autorizar($requerimiento, $casoUso);

        return view('requerimientos.casos-uso.edit', compact('requerimiento', 'casoUso'));
    }

    public function update(CasoUsoRequest $request, Requerimiento $requerimiento, CasoUso $casoUso)
    {
        $this->autorizar($requerimiento, $casoUso);

        $casoUso->update($request->validated());

        return redirect()->to(route('requerimientos.show', $requerimiento).'#casos-uso')
            ->with('success', 'Caso de uso actualizado correctamente.');
    }

    public function destroy(Requerimiento $requerimiento, CasoUso $casoUso): JsonResponse
    {
        $this->autorizar($requerimiento, $casoUso);

        $casoUso->delete();

        return response()->json(['message' => 'Caso de uso eliminado correctamente.']);
    }

    private function autorizar(Requerimiento $requerimiento, CasoUso $casoUso): void
    {
        abort_unless($casoUso->requerimiento_id === $requerimiento->id, 404);

        $this->authorize('gestionarCasosUso', $requerimiento);
    }
}
