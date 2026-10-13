<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Con driver database, si la fila de sesión fue eliminada (p. ej. invalidación administrativa),
 * cierra la autenticación y redirige al login.
 */
class EnsureSessionPersisted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            Auth::check()
            && $request->hasSession()
            && config('session.driver') === 'database'
        ) {
            $exists = DB::table(config('session.table', 'sessions'))
                ->where('id', $request->session()->getId())
                ->exists();

            if (! $exists) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->with('warning', 'Su sesión fue cerrada. Vuelva a iniciar sesión.');
            }
        }

        return $next($request);
    }
}
