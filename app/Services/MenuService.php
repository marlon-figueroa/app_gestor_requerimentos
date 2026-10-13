<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuService
{
    public const NIVEL_MAXIMO = 3;

    public function __construct(private readonly Request $request) {}

    /**
     * Devuelve el árbol del menú visible para el usuario, con el elemento activo
     * marcado y sus ramas ancestras abiertas.
     *
     * @return array<int, array<string, mixed>>
     */
    public function construir(?Authenticatable $usuario, ?array $elementos = null): array
    {
        return $this->procesar($elementos ?? config('menu', []), $usuario, 1, 'menu');
    }

    private function procesar(array $elementos, ?Authenticatable $usuario, int $nivel, string $prefijo): array
    {
        $resultado = [];

        foreach ($elementos as $indice => $elemento) {
            if (! $this->puedeVer($elemento, $usuario)) {
                continue;
            }

            $id = $prefijo.'-'.(Str::slug($elemento['label'] ?? '') ?: $indice);
            $hijos = [];

            if (! empty($elemento['children']) && $nivel < self::NIVEL_MAXIMO) {
                $hijos = $this->procesar($elemento['children'], $usuario, $nivel + 1, $id);

                if ($hijos === []) {
                    continue;
                }
            } elseif (empty($elemento['route'])) {
                continue;
            }

            $activo = $hijos === [] && $this->request->routeIs($elemento['active'] ?? $elemento['route']);

            $resultado[] = [
                'id' => $id,
                'label' => $elemento['label'],
                'icon' => $elemento['icon'] ?? 'circle',
                'url' => $hijos === [] ? route($elemento['route']) : null,
                'nivel' => $nivel,
                'activo' => $activo,
                'abierto' => collect($hijos)->contains(fn ($hijo) => $hijo['activo'] || $hijo['abierto']),
                'children' => $hijos,
            ];
        }

        return $resultado;
    }

    private function puedeVer(array $elemento, ?Authenticatable $usuario): bool
    {
        if (! isset($elemento['can'])) {
            return true;
        }

        return $usuario !== null && $usuario->canAny((array) $elemento['can']);
    }
}
