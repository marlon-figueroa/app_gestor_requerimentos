<?php

namespace App\Models;

use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Model;

class Paginador
{
    /**
     * Limite de registros por pagina para evitar consultas desmedidas.
     */
    public const MAX_PAGE_LENGTH = 500;

    public $pageNumber;

    public $pageLength;

    public $skid;

    public $totalRecords;

    public $totalPages;

    public $query;

    public $data;

    /**
     * Crea el paginador a partir de los datos del formulario (page, pageLength).
     *
     * @param  array|\ArrayAccess  $formulario
     */
    public function __construct($formulario)
    {
        $this->pageNumber = max(1, (int) ($formulario['page'] ?? 1));
        $this->pageLength = min(self::MAX_PAGE_LENGTH, max(1, (int) ($formulario['pageLength'] ?? 10)));
        $this->skid = ($this->pageNumber - 1) * $this->pageLength;
        $this->totalRecords = 0;
        $this->totalPages = 0;
    }

    /**
     * Devuelve una instancia del modelo; acepta el nombre de la clase o una instancia.
     */
    public function model($model)
    {
        return is_string($model) && is_subclass_of($model, Model::class) ? new $model : $model;
    }

    /**
     * Guarda la consulta. Si es un query builder (Eloquent o Query Builder),
     * calcula el total y obtiene los datos de la pagina actual con skip/take.
     */
    public function setQuery($query)
    {
        $this->query = $query;

        if ($query instanceof BuilderContract) {
            $this->setTotalRecords((clone $query)->count());
            $this->setData((clone $query)->skip($this->skid)->take($this->pageLength)->get());
        }
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setTotalRecords($total)
    {
        $this->totalRecords = (int) $total;
        $this->totalPages = (int) ceil($this->totalRecords / $this->pageLength);
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function toArray()
    {
        return [
            'pageNumber' => $this->pageNumber,
            'pageLength' => $this->pageLength,
            'skid' => $this->skid,
            'totalRecords' => $this->totalRecords,
            'totalPages' => $this->totalPages,
            'query' => $this->query instanceof BuilderContract ? null : $this->query,
            'data' => $this->data,
        ];
    }

    public function toString()
    {
        return json_encode($this->toArray());
    }
}
