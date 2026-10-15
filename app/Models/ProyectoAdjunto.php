<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProyectoAdjunto extends Model
{
    /**
     * Disco privado: los archivos solo se sirven a través de rutas autenticadas.
     */
    public const DISCO = 'local';

    public const MIMES_IMAGEN = ['image/png', 'image/jpeg'];

    protected $table = 'proyecto_adjuntos';

    protected $fillable = [
        'nombre_original',
        'ruta',
        'mime',
        'tamano',
        'subido_por',
    ];

    protected static function booted(): void
    {
        static::deleted(function (ProyectoAdjunto $adjunto): void {
            Storage::disk(self::DISCO)->delete($adjunto->ruta);
        });
    }

    public static function guardarSubida(Proyecto $proyecto, UploadedFile $archivo, User $usuario): self
    {
        $ruta = $archivo->store("proyectos/{$proyecto->id}/adjuntos", self::DISCO);

        return $proyecto->adjuntos()->create([
            'nombre_original' => $archivo->getClientOriginalName(),
            'ruta' => $ruta,
            'mime' => $archivo->getMimeType(),
            'tamano' => $archivo->getSize(),
            'subido_por' => $usuario->id,
        ]);
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function esImagen(): bool
    {
        return in_array($this->mime, self::MIMES_IMAGEN, true);
    }

    public function esPdf(): bool
    {
        return $this->mime === 'application/pdf';
    }

    public function existe(): bool
    {
        return Storage::disk(self::DISCO)->exists($this->ruta);
    }

    public function tamanoLegible(): string
    {
        $kb = $this->tamano / 1024;

        return $kb >= 1024 ? number_format($kb / 1024, 1).' MB' : number_format($kb, 0).' KB';
    }
}
