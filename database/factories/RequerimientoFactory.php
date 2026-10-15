<?php

namespace Database\Factories;

use App\Enums\EstadoRequerimiento;
use App\Models\Requerimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Requerimiento>
 */
class RequerimientoFactory extends Factory
{
    public function definition(): array
    {
        $solicitud = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'titulo' => fake()->sentence(5),
            'descripcion' => fake()->paragraph(),
            'fecha_solicitud' => $solicitud,
            'fecha_vencimiento' => fake()->dateTimeBetween($solicitud, '+2 months'),
            'solicitante_id' => User::factory(),
        ];
    }

    public function estado(EstadoRequerimiento $estado, ?User $gestor = null): static
    {
        return $this->afterMaking(function (Requerimiento $requerimiento) use ($estado, $gestor): void {
            $requerimiento->estado = $estado;
            $requerimiento->gestor_id = $gestor?->id;
        });
    }
}
