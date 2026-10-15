<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProyectoEvaluacionPf extends Model
{
    protected $table = 'proyecto_evaluaciones_pf';

    protected $fillable = [
        'proyecto_id',
        'nombre',
        'notas',
        'creado_por',
        'total_sin_ajuste',
        'suma_influencia',
        'factor_ajuste',
        'total_ajustado',
        'caracteristicas',
    ];

    protected function casts(): array
    {
        return [
            'caracteristicas' => 'array',
            'factor_ajuste' => 'decimal:2',
            'total_ajustado' => 'decimal:2',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function componentes(): HasMany
    {
        return $this->hasMany(ProyectoEvaluacionComponentePf::class, 'evaluacion_id')->orderBy('orden');
    }
}
