<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RequerimientoAdjunto extends Model
{
    /**
     * Disco privado: los archivos solo se sirven a través de rutas autenticadas.
     */
    public const DISCO = 'local';

    public const MIMES_IMAGEN = ['image/png', 'image/jpeg'];

    protected $table = 'requerimiento_adjuntos';

    protected $fillable = [
        'nombre_original',
        'ruta',
        'mime',
        'tamano',
        'subido_por',
    ];

    protected static function booted(): void
    {
        static::deleted(function (RequerimientoAdjunto $adjunto): void {
            Storage::disk(self::DISCO)->delete($adjunto->ruta);
        });
    }

    public static function guardarSubida(Requerimiento $requerimiento, UploadedFile $archivo, User $usuario): self
    {
        $ruta = $archivo->store("requerimientos/{$requerimiento->id}/adjuntos", self::DISCO);

        return $requerimiento->adjuntos()->create([
            'nombre_original' => $archivo->getClientOriginalName(),
            'ruta' => $ruta,
            'mime' => $archivo->getMimeType(),
            'tamano' => $archivo->getSize(),
            'subido_por' => $usuario->id,
        ]);
    }

    public function requerimiento(): BelongsTo
    {
        return $this->belongsTo(Requerimiento::class);
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

    /**
     * Contenido como data URI para incrustarlo en el PDF exportado.
     */
    public function dataUri(): ?string
    {
        if (! $this->esImagen() || ! $this->existe()) {
            return null;
        }

        return 'data:'.$this->mime.';base64,'.base64_encode(Storage::disk(self::DISCO)->get($this->ruta));
    }

    public function tamanoLegible(): string
    {
        $kb = $this->tamano / 1024;

        return $kb >= 1024 ? number_format($kb / 1024, 1).' MB' : number_format($kb, 0).' KB';
    }
}
