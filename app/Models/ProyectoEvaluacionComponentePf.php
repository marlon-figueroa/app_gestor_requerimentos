<?php

namespace App\Models;

use App\Enums\ComplejidadPf;
use App\Enums\TipoComponentePf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProyectoEvaluacionComponentePf extends Model
{
    protected $table = 'proyecto_evaluacion_componentes_pf';

    protected $fillable = [
        'evaluacion_id',
        'tipo',
        'nombre',
        'descripcion',
        'det',
        'referencias',
        'complejidad',
        'puntos',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoComponentePf::class,
            'complejidad' => ComplejidadPf::class,
        ];
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(ProyectoEvaluacionPf::class, 'evaluacion_id');
    }
}
