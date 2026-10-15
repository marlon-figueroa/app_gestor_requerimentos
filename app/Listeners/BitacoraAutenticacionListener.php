<?php

namespace App\Listeners;

use App\Services\BitacoraService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;

class BitacoraAutenticacionListener
{
    public function __construct(private BitacoraService $bitacora) {}

    public function handleLogin(Login $event): void
    {
        $this->bitacora->registrar('login', 'Inicio de sesión', [
            'usuario_id' => $event->user->getAuthIdentifier(),
            'modulo' => 'autenticacion',
            'descripcion' => "El usuario {$event->user->email} inició sesión.",
            'datos_adicionales' => ['recordar' => $event->remember],
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        $this->bitacora->registrar('logout', 'Cierre de sesión', [
            'usuario_id' => $event->user->getAuthIdentifier(),
            'modulo' => 'autenticacion',
            'descripcion' => "El usuario {$event->user->email} cerró sesión.",
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        $this->bitacora->registrar('login_fallido', 'Intento de inicio de sesión fallido', [
            'usuario_id' => $event->user?->getAuthIdentifier(),
            'modulo' => 'autenticacion',
            'descripcion' => "Credenciales inválidas para {$email}.",
            'exitoso' => false,
            'error_mensaje' => $event->user ? 'Contraseña incorrecta' : 'Usuario no registrado',
            'datos_adicionales' => ['email' => $email],
        ]);
    }

    public function handleTakeImpersonation(TakeImpersonation $event): void
    {
        $this->bitacora->registrar('impersonar', 'Suplantación de usuario', [
            'usuario_id' => $event->impersonator->getAuthIdentifier(),
            'modulo' => 'autenticacion',
            'descripcion' => "{$event->impersonator->email} ingresó como {$event->impersonated->email}.",
            'modelo_type' => $event->impersonated->getMorphClass(),
            'modelo_id' => $event->impersonated->getAuthIdentifier(),
            'datos_adicionales' => [
                'impersonador_id' => $event->impersonator->getAuthIdentifier(),
                'suplantado_id' => $event->impersonated->getAuthIdentifier(),
            ],
        ]);
    }

    public function handleLeaveImpersonation(LeaveImpersonation $event): void
    {
        $this->bitacora->registrar('fin_impersonacion', 'Fin de suplantación', [
            'usuario_id' => $event->impersonator->getAuthIdentifier(),
            'modulo' => 'autenticacion',
            'descripcion' => "{$event->impersonator->email} volvió de la sesión de {$event->impersonated->email}.",
            'modelo_type' => $event->impersonated->getMorphClass(),
            'modelo_id' => $event->impersonated->getAuthIdentifier(),
            'datos_adicionales' => [
                'impersonador_id' => $event->impersonator->getAuthIdentifier(),
                'suplantado_id' => $event->impersonated->getAuthIdentifier(),
            ],
        ]);
    }

    public function handleLockout(Lockout $event): void
    {
        $email = $event->request->input('email');

        $this->bitacora->registrar('bloqueo', 'Bloqueo por exceso de intentos', [
            'usuario_id' => null,
            'modulo' => 'autenticacion',
            'descripcion' => "Se bloqueó temporalmente el acceso para {$email}.",
            'exitoso' => false,
            'status_code' => 429,
            'datos_adicionales' => ['email' => $email],
        ]);
    }
}
