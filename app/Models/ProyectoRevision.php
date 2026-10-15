<?php

namespace App\Models;

use App\Enums\EstadoProyecto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProyectoRevision extends Model
{
    public const UPDATED_AT = null;

    public const ACCIONES = [
        'crear' => 'Creó el proyecto',
        'finalizar' => 'Finalizó el proyecto',
        'cancelar' => 'Canceló el proyecto',
    ];

    protected $table = 'proyecto_revisiones';

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
            'estado_anterior' => EstadoProyecto::class,
            'estado_nuevo' => EstadoProyecto::class,
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
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
