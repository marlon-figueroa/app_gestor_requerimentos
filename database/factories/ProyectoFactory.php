<?php

namespace Database\Factories;

use App\Enums\EstadoProyecto;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proyecto>
 */
class ProyectoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->sentence(3),
            'descripcion' => fake()->paragraph(),
            'responsable_id' => User::factory(),
            'fecha_finalizacion' => fake()->dateTimeBetween('+1 month', '+6 months'),
        ];
    }

    public function estado(EstadoProyecto $estado): static
    {
        return $this->afterMaking(function (Proyecto $proyecto) use ($estado): void {
            $proyecto->estado = $estado;
        });
    }
}
