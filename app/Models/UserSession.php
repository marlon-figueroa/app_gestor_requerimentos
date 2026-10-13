<?php

namespace App\Models;

use App\Support\AgenteUsuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserSession extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'sessions';

    public $timestamps = false;

    protected $fillable = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_activity' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActivas($query)
    {
        $desde = now()->subMinutes((int) config('session.lifetime', 120))->timestamp;

        return $query
            ->where('last_activity', '>=', $desde)
            ->whereNotNull('user_id');
    }

    public function getLastActivityAtAttribute(): ?Carbon
    {
        return $this->last_activity
            ? Carbon::createFromTimestamp($this->last_activity)
            : null;
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    public function analizarAgente(): array
    {
        return AgenteUsuario::analizar($this->user_agent);
    }

    public function resumenCliente(): string
    {
        [$navegador, $sistema, $dispositivo] = $this->analizarAgente();

        return collect([$navegador, $sistema, $dispositivo])
            ->filter()
            ->implode(' · ');
    }
}
