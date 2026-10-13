<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
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

    public function photoUrl(): string
    {
        if (filled($this->path_photo)) {
            return Storage::disk('public')->url($this->path_photo);
        }

        return asset('image/user_image.png');
    }

    public static function borrarFoto(?string $ruta): void
    {
        if (filled($ruta) && str_starts_with($ruta, self::PHOTO_DIRECTORY.'/')) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
