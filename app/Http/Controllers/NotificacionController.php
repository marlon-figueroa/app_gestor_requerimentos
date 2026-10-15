<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): JsonResponse
    {
        $notificaciones = $request->user()
            ->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'read_at' => $n->read_at,
                'data' => $n->data,
                'created_at' => $n->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'no_leidas' => $request->user()->unreadNotifications()->count(),
            'items' => $notificaciones,
        ]);
    }

    public function marcarLeida(Request $request, string $id): JsonResponse
    {
        $notificacion = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notificacion->markAsRead();

        return response()->json(['message' => 'Notificación marcada como leída.']);
    }

    public function marcarTodasLeidas(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Todas las notificaciones fueron marcadas como leídas.']);
    }
}
