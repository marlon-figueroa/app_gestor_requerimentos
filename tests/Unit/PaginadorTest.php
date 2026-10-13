<?php

namespace Tests\Unit;

use App\Models\Paginador;
use PHPUnit\Framework\TestCase;

class PaginadorTest extends TestCase
{
    public function test_calcula_desplazamiento_y_total_de_paginas(): void
    {
        $paginador = new Paginador(['page' => 3, 'pageLength' => 10]);
        $paginador->setTotalRecords(25);

        $this->assertSame(20, $paginador->skid);
        $this->assertSame(25, $paginador->totalRecords);
        $this->assertSame(3, $paginador->totalPages);
    }

    public function test_normaliza_valores_invalidos(): void
    {
        $paginador = new Paginador(['page' => '0', 'pageLength' => '100000']);

        $this->assertSame(1, $paginador->pageNumber);
        $this->assertSame(Paginador::MAX_PAGE_LENGTH, $paginador->pageLength);
        $this->assertSame(0, $paginador->skid);
    }
}
