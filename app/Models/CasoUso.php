<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasoUso extends Model
{
    protected $table = 'casos_uso';

    protected $fillable = [
        'codigo',
        'nombre',
        'pantalla',
        'actor',
        'descripcion',
        'precondiciones',
        'flujo_principal',
        'postcondiciones',
        'orden',
    ];

    public function requerimiento(): BelongsTo
    {
        return $this->belongsTo(Requerimiento::class);
    }
}
