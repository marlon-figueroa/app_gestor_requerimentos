<?php

namespace App\Http\Controllers;

use App\Http\Requests\Perfil\ActualizarPerfilRequest;
use App\Http\Requests\Perfil\CambiarContrasenaRequest;
use App\Models\User;
use App\Services\PerfilService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class PerfilController extends Controller
{
    public function show(PerfilService $perfil): View
    {
        $usuario = auth()->user()->load('roles');

        $limiteReq = $perfil->limiteRequerimientosRecientes();
        $estadisticas = $perfil->estadisticas($usuario);

        return view('perfil.show', [
            'usuario' => $usuario,
            'estadisticas' => $estadisticas,
            'proyectos' => $perfil->proyectosInvolucrados($usuario),
            'requerimientos' => $perfil->requerimientosRecientes($usuario, $limiteReq),
            'limiteRequerimientos' => $limiteReq,
            'hayMasRequerimientos' => $estadisticas['requerimientos'] > $limiteReq,
        ]);
    }

    public function update(ActualizarPerfilRequest $request): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();
        $data = $request->safe()->only(['name', 'email']);
        $fotoAnterior = $usuario->path_photo;
        $fotoNueva = $request->hasFile('photo')
            ? User::guardarFotoSubida($request->file('photo'))
            : null;

        if ($fotoNueva) {
            $data['path_photo'] = $fotoNueva;
        }

        try {
            $usuario->update($data);
        } catch (Throwable $e) {
            User::borrarFoto($fotoNueva);

            throw $e;
        }

        if ($fotoNueva && $fotoAnterior !== $fotoNueva) {
            User::borrarFoto($fotoAnterior);
        }

        return redirect()
            ->route('perfil.show')
            ->with('success', 'Tu perfil se actualizó correctamente.');
    }

    public function updatePassword(CambiarContrasenaRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()
            ->to(route('perfil.show').'#seguridad')
            ->with('success', 'Contraseña actualizada correctamente.');
    }
}
