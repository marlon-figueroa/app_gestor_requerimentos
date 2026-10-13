<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bitacora extends Model
{
    public const TIPOS_ACCION = [
        'crear' => 'Creación',
        'actualizar' => 'Actualización',
        'eliminar' => 'Eliminación',
        'personalizada' => 'Personalizada',
        'login' => 'Ingreso',
        'logout' => 'Salida',
        'login_fallido' => 'Login fallido',
        'bloqueo' => 'Bloqueo',
    ];

    protected $table = 'bitacoras';

    protected $fillable = [
        'usuario_id',
        'accion',
        'descripcion',
        'ip_address',
        'user_agent',
        'modelo_type',
        'modelo_id',
        'modulo',
        'tipo_accion',
        'metodo_http',
        'ruta',
        'valores_anteriores',
        'valores_nuevos',
        'exitoso',
        'status_code',
        'error_mensaje',
        'tiempo_ejecucion',
        'sesion_id',
        'navegador',
        'sistema_operativo',
        'dispositivo',
        'datos_adicionales',
        'tags',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valores_anteriores' => 'array',
            'valores_nuevos' => 'array',
            'datos_adicionales' => 'array',
            'exitoso' => 'boolean',
            'status_code' => 'integer',
            'tiempo_ejecucion' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public static function etiquetaTipoAccion(?string $tipo): string
    {
        if ($tipo === null || $tipo === '') {
            return 'N/A';
        }

        return self::TIPOS_ACCION[$tipo] ?? ucfirst($tipo);
    }

    /**
     * Relación con el usuario que realizó la acción
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación polimórfica con el modelo afectado
     */
    public function modelo(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope para filtrar por tipo de acción
     */
    public function scopePorTipoAccion($query, string $tipo)
    {
        return $query->where('tipo_accion', $tipo);
    }

    /**
     * Scope para filtrar por módulo
     */
    public function scopePorModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopePorUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope para filtrar solo acciones exitosas
     */
    public function scopeExitosas($query)
    {
        return $query->where('exitoso', true);
    }

    /**
     * Scope para filtrar solo acciones fallidas
     */
    public function scopeFallidas($query)
    {
        return $query->where('exitoso', false);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('created_at', [$desde, $hasta]);
    }
}
