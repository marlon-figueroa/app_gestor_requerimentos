<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Carpeta del disco public donde se guardan las fotos.
     */
    public const PHOTO_DIRECTORY = 'usuarios/fotos';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'path_photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            self::borrarFoto($user->path_photo);
        });
    }

    /**
     * Guarda la imagen en el disco public y devuelve la ruta relativa
     * (usuarios/fotos/{nombre}) que debe persistirse en path_photo.
     */
    public static function guardarFotoSubida(UploadedFile $file): string
    {
        $nombre = $file->hashName();
        $file->storeAs(self::PHOTO_DIRECTORY, $nombre, 'public');

        return self::PHOTO_DIRECTORY.'/'.$nombre;
    }

    public function photoUrl(): string
    {
        $ruta = self::normalizarRutaFoto($this->path_photo);

        if ($ruta !== null && Storage::disk('public')->exists($ruta)) {
            return Storage::disk('public')->url($ruta);
        }

        return asset('image/user_image.png');
    }

    public function requerimientosSolicitados(): HasMany
    {
        return $this->hasMany(Requerimiento::class, 'solicitante_id');
    }

    public function requerimientosGestionados(): HasMany
    {
        return $this->hasMany(Requerimiento::class, 'gestor_id');
    }

    public static function borrarFoto(?string $ruta): void
    {
        $ruta = self::normalizarRutaFoto($ruta);

        if ($ruta !== null && Storage::disk('public')->exists($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }

    /**
     * path_photo debe ser la ruta relativa al disco public (carpeta + nombre aleatorio).
     */
    public static function normalizarRutaFoto(?string $ruta): ?string
    {
        if (! filled($ruta)) {
            return null;
        }

        $ruta = str_replace('\\', '/', trim($ruta));

        if (str_starts_with($ruta, self::PHOTO_DIRECTORY.'/')) {
            return $ruta;
        }

        if (! str_contains($ruta, '/')) {
            return self::PHOTO_DIRECTORY.'/'.$ruta;
        }

        return $ruta;
    }
}
