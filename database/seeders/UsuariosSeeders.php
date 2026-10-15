<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsuariosSeeders extends Seeder
{
    public const ADMINISTRADORES = [
        'admin@example.com' => 'Administrador',
    ];

    public const GESTORES = [
        'ana.martinez@example.com' => 'Ana Martínez',
        'carlos.rodriguez@example.com' => 'Carlos Rodríguez',
        'lucia.hernandez@example.com' => 'Lucía Hernández',
        'diego.ramirez@example.com' => 'Diego Ramírez',
    ];

    public function run(): void
    {
        $this->crear(self::ADMINISTRADORES, 'Administrador');
        $this->crear(self::GESTORES, 'Gestor');
    }

    private function crear(array $usuarios, string $rol): void
    {
        foreach ($usuarios as $email => $nombre) {
            User::firstOrCreate(
                ['email' => $email],
                ['name' => $nombre, 'password' => 'password', 'email_verified_at' => now()],
            )->assignRole($rol);
        }
    }
}
