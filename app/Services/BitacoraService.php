<?php

namespace App\Services;

use App\Models\Bitacora;
use App\Support\AgenteUsuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;

class BitacoraService
{
    /**
     * Atributos que nunca se guardan en valores_anteriores / valores_nuevos.
     */
    private const ATRIBUTOS_OCULTOS = ['remember_token', 'updated_at', 'created_at'];

    /**
     * Atributos que solo se registran como modificados, sin su valor real.
     */
    private const ATRIBUTOS_ENMASCARADOS = ['password'];

    public function __construct(private Request $request) {}

    /**
     * Registra una entrada en la bitacora. Nunca interrumpe el flujo: si falla, solo se reporta.
     */
    public function registrar(string $tipoAccion, string $accion, array $datos = []): ?Bitacora
    {
        try {
            [$navegador, $sistemaOperativo, $dispositivo] = AgenteUsuario::analizar($this->request->userAgent());

            return Bitacora::create([
                'usuario_id' => $datos['usuario_id'] ?? auth()->id(),
                'tipo_accion' => $tipoAccion,
                'accion' => $accion,
                'ip_address' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'metodo_http' => app()->runningInConsole() ? null : $this->request->method(),
                'ruta' => app()->runningInConsole() ? null : $this->request->path(),
                'sesion_id' => $this->request->hasSession() ? $this->request->session()->getId() : null,
                'navegador' => $navegador,
                'sistema_operativo' => $sistemaOperativo,
                'dispositivo' => $dispositivo,
                'tiempo_ejecucion' => defined('LARAVEL_START') ? (int) round((microtime(true) - LARAVEL_START) * 1000) : null,
                'exitoso' => true,
                ...$datos,
            ]);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Registra la creacion, actualizacion o eliminacion de un modelo.
     */
    public function registrarModelo(string $tipoAccion, Model $modelo, string $modulo, ?array $anteriores = null, ?array $nuevos = null): ?Bitacora
    {
        $etiqueta = $this->etiquetaModelo($modelo);

        return $this->registrar($tipoAccion, Bitacora::etiquetaTipoAccion($tipoAccion)." de {$modulo}", [
            'descripcion' => "{$etiqueta} (ID {$modelo->getKey()})",
            'modulo' => $modulo,
            'modelo_type' => $modelo->getMorphClass(),
            'modelo_id' => $modelo->getKey(),
            'valores_anteriores' => $anteriores !== null ? $this->limpiar($anteriores) : null,
            'valores_nuevos' => $nuevos !== null ? $this->limpiar($nuevos) : null,
        ]);
    }

    /**
     * Registra el cambio de una relacion (por ejemplo roles de un usuario) solo si hubo diferencias.
     */
    public function registrarCambioRelacion(Model $modelo, string $modulo, string $campo, array $antes, array $despues): ?Bitacora
    {
        sort($antes);
        sort($despues);

        if ($antes === $despues) {
            return null;
        }

        return $this->registrar('actualizar', "Actualización de {$campo} en {$modulo}", [
            'descripcion' => $this->etiquetaModelo($modelo)." (ID {$modelo->getKey()})",
            'modulo' => $modulo,
            'modelo_type' => $modelo->getMorphClass(),
            'modelo_id' => $modelo->getKey(),
            'valores_anteriores' => [$campo => $antes],
            'valores_nuevos' => [$campo => $despues],
            'tags' => $campo,
        ]);
    }

    private function limpiar(array $valores): array
    {
        $valores = array_diff_key($valores, array_flip(self::ATRIBUTOS_OCULTOS));

        foreach (self::ATRIBUTOS_ENMASCARADOS as $atributo) {
            if (array_key_exists($atributo, $valores)) {
                $valores[$atributo] = '********';
            }
        }

        return $valores;
    }

    private function etiquetaModelo(Model $modelo): string
    {
        return (string) ($modelo->getAttribute('name') ?? $modelo->getAttribute('nombre') ?? class_basename($modelo));
    }

}
