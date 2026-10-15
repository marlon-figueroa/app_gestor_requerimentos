<?php

namespace App\Models;

use App\Enums\EstadoRequerimiento;
use Database\Factories\RequerimientoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requerimiento extends Model
{
    /** @use HasFactory<RequerimientoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'requerimientos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_solicitud',
        'fecha_vencimiento',
        'solicitante_id',
    ];

    protected $attributes = [
        'estado' => 'borrador',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoRequerimiento::class,
            'fecha_solicitud' => 'date',
            'fecha_vencimiento' => 'date',
            'enviado_at' => 'datetime',
            'aprobado_at' => 'datetime',
            'finalizado_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Requerimiento $requerimiento): void {
            $requerimiento->forceFill(['codigo' => sprintf('REQ-%04d', $requerimiento->id)])->saveQuietly();
        });

        static::forceDeleting(function (Requerimiento $requerimiento): void {
            $requerimiento->adjuntos()->get()->each->delete();
        });
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function casosUso(): HasMany
    {
        return $this->hasMany(CasoUso::class)->orderBy('orden')->orderBy('id');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(RequerimientoAdjunto::class)->orderBy('id');
    }

    public function revisiones(): HasMany
    {
        return $this->hasMany(RequerimientoRevision::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Vencido: pasó la fecha límite y aún no se finaliza.
     */
    protected function vencido(): Attribute
    {
        return Attribute::get(fn () => $this->estado !== EstadoRequerimiento::Finalizado
            && $this->fecha_vencimiento !== null
            && $this->fecha_vencimiento->lt(today()));
    }

    public function enBandeja(): bool
    {
        return $this->estado === EstadoRequerimiento::EnRevision && $this->gestor_id === null;
    }

    /**
     * Los gestores ven todo; el resto solo lo que solicitó.
     */
    public function scopeVisiblesPara(Builder $query, User $usuario): Builder
    {
        if ($usuario->can('requerimientos.revisar')) {
            return $query;
        }

        return $query->where('requerimientos.solicitante_id', $usuario->id);
    }

    public function scopeBandeja(Builder $query): Builder
    {
        return $query->where('estado', EstadoRequerimiento::EnRevision)->whereNull('gestor_id');
    }

    public function siguienteCodigoCasoUso(): string
    {
        $ultimo = $this->casosUso()->reorder()->pluck('codigo')
            ->map(fn (string $codigo) => (int) preg_replace('/\D/', '', $codigo))
            ->max() ?? 0;

        return sprintf('CU-%02d', $ultimo + 1);
    }
}
