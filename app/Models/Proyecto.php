<?php

namespace App\Models;

use App\Enums\EstadoProyecto;
use App\Enums\EstadoRequerimiento;
use App\Models\User;
use Database\Factories\ProyectoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyecto extends Model
{
    /** @use HasFactory<ProyectoFactory> */
    use HasFactory;

    protected $table = 'proyectos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'responsable_id',
        'fecha_inicio',
        'fecha_finalizacion',
    ];

    protected $attributes = [
        'estado' => 'activo',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoProyecto::class,
            'fecha_inicio' => 'date',
            'fecha_finalizacion' => 'date',
            'finalizado_at' => 'datetime',
            'cancelado_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Proyecto $proyecto): void {
            $proyecto->forceFill(['codigo' => sprintf('PRY-%04d', $proyecto->id)])->saveQuietly();
        });
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function requerimientos(): HasMany
    {
        return $this->hasMany(Requerimiento::class)->orderBy('id');
    }

    /**
     * Requerimientos que impiden finalizar el proyecto. Los eliminados quedan fuera por SoftDeletes.
     */
    public function requerimientosPendientes(): HasMany
    {
        return $this->hasMany(Requerimiento::class)->where('estado', '!=', EstadoRequerimiento::Finalizado);
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(ProyectoAdjunto::class)->orderBy('id');
    }

    public function revisiones(): HasMany
    {
        return $this->hasMany(ProyectoRevision::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function evaluacionesPf(): HasMany
    {
        return $this->hasMany(ProyectoEvaluacionPf::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function miembros(): HasMany
    {
        return $this->hasMany(ProyectoMiembro::class);
    }

    public function scopeAccesiblesPara(Builder $query, User $usuario): Builder
    {
        if ($usuario->can('proyectos.ver')) {
            if (config('proyectos.scope_responsable') && ! $usuario->hasRole('Administrador') && $usuario->can('proyectos.crear')) {
                return $query->where(function (Builder $q) use ($usuario): void {
                    $q->where('responsable_id', $usuario->id)
                        ->orWhereHas('miembros', fn (Builder $m) => $m->where('user_id', $usuario->id));
                });
            }

            return $query;
        }

        if ($usuario->can('proyectos.ver_limitado')) {
            return $query->where(function (Builder $q) use ($usuario): void {
                $q->whereHas('requerimientos', fn (Builder $r) => $r->where('solicitante_id', $usuario->id))
                    ->orWhereHas('miembros', fn (Builder $m) => $m->where('user_id', $usuario->id));
            });
        }

        return $query->whereRaw('0 = 1');
    }

    /**
     * Vencido: pasó la fecha de finalización y sigue activo.
     */
    protected function vencido(): Attribute
    {
        return Attribute::get(fn () => $this->estado === EstadoProyecto::Activo
            && $this->fecha_finalizacion !== null
            && $this->fecha_finalizacion->lt(today()));
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('proyectos.estado', EstadoProyecto::Activo);
    }

    /**
     * @return array<int, string>
     */
    public static function opcionesActivas(?int $incluir = null): array
    {
        return static::query()
            ->where(fn (Builder $q) => $q->activos()->when($incluir, fn (Builder $q) => $q->orWhere('id', $incluir)))
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre'])
            ->mapWithKeys(fn (Proyecto $proyecto) => [$proyecto->id => "{$proyecto->codigo} · {$proyecto->nombre}"])
            ->all();
    }
}
