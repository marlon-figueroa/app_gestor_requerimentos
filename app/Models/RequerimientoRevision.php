<?php

namespace App\Models;

use App\Enums\EstadoRequerimiento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequerimientoRevision extends Model
{
    public const UPDATED_AT = null;

    public const ACCIONES = [
        'crear' => 'Creó el requerimiento',
        'enviar' => 'Envió a revisión',
        'tomar' => 'Tomó el requerimiento para revisión',
        'aprobar' => 'Aprobó',
        'rechazar' => 'Rechazó',
        'iniciar' => 'Inició el desarrollo',
        'finalizar' => 'Finalizó',
    ];

    protected $table = 'requerimiento_revisiones';

    protected $fillable = [
        'usuario_id',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'estado_anterior' => EstadoRequerimiento::class,
            'estado_nuevo' => EstadoRequerimiento::class,
        ];
    }

    public function requerimiento(): BelongsTo
    {
        return $this->belongsTo(Requerimiento::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function etiquetaAccion(): string
    {
        return self::ACCIONES[$this->accion] ?? $this->accion;
    }
}
