<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Throwable;

trait FiltraPorFechas
{
    /**
     * Aplica los filtros fecha_desde / fecha_hasta (formato dd/mm/aaaa) sobre la columna indicada.
     */
    protected function filtrarPorFechas(Builder $query, Request $request, string $columna): Builder
    {
        return $query
            ->when($this->parseDate($request->input('fecha_desde')), fn (Builder $q, Carbon $date) => $q->where($columna, '>=', $date->startOfDay()))
            ->when($this->parseDate($request->input('fecha_hasta')), fn (Builder $q, Carbon $date) => $q->where($columna, '<=', $date->endOfDay()));
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value);
        } catch (Throwable) {
            return null;
        }
    }
}
