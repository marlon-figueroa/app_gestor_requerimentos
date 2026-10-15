<?php

namespace Tests\Unit;

use App\Enums\ComplejidadPf;
use App\Enums\TipoComponentePf;
use App\Services\ProyectoPuntosFuncionService;
use PHPUnit\Framework\TestCase;

class ProyectoPuntosFuncionServiceTest extends TestCase
{
    private ProyectoPuntosFuncionService $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->servicio = new ProyectoPuntosFuncionService;
    }

    public function test_ei_baja_con_pocos_det_y_ftr(): void
    {
        $resultado = $this->servicio->evaluarComponente(TipoComponentePf::Ei, 4, 1);

        $this->assertSame(ComplejidadPf::Baja, $resultado['complejidad']);
        $this->assertSame(3, $resultado['puntos']);
    }

    public function test_eo_alta_en_celda_limite(): void
    {
        $resultado = $this->servicio->evaluarComponente(TipoComponentePf::Eo, 16, 3);

        $this->assertSame(ComplejidadPf::Alta, $resultado['complejidad']);
        $this->assertSame(7, $resultado['puntos']);
    }

    public function test_ilf_media(): void
    {
        $resultado = $this->servicio->evaluarComponente(TipoComponentePf::Ilf, 25, 3);

        $this->assertSame(ComplejidadPf::Media, $resultado['complejidad']);
        $this->assertSame(10, $resultado['puntos']);
    }

    public function test_factor_ajuste_minimo_y_maximo(): void
    {
        $minimo = $this->servicio->factorAjuste(array_fill(0, 14, 0));
        $maximo = $this->servicio->factorAjuste(array_fill(0, 14, 5));

        $this->assertSame(0, $minimo['suma_influencia']);
        $this->assertSame(0.65, $minimo['factor_ajuste']);
        $this->assertSame(70, $maximo['suma_influencia']);
        $this->assertSame(1.35, $maximo['factor_ajuste']);
    }

    public function test_total_ajustado_redondea(): void
    {
        $this->assertSame(134.55, $this->servicio->totalAjustado(100, 1.3455));
    }
}
