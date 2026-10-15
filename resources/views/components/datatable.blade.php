@props([
    'id',
    'url',
    'columns' => [],
    'order' => [[0, 'asc']],
    'filters' => null,
    'pageLength' => null,
    'card' => true,
])

@php
    $columnas = collect($columns)->map(function (array $columna) {
        $acciones = ! empty($columna['actions']);

        return [
            'label' => $columna['label'] ?? '',
            'headerClass' => $columna['headerClass'] ?? ($acciones ? 'text-end' : ($columna['class'] ?? null)),
            'config' => array_filter([
                'data' => $columna['data'],
                'name' => $columna['name'] ?? ($acciones ? null : $columna['data']),
                'orderable' => $acciones ? false : ($columna['orderable'] ?? null),
                'searchable' => $acciones ? false : ($columna['searchable'] ?? null),
                'className' => trim(($acciones ? 'acciones text-end ' : '').($columna['class'] ?? '')) ?: null,
                'defaultContent' => $columna['defaultContent'] ?? null,
            ], fn ($valor) => $valor !== null),
        ];
    });

    $config = array_filter([
        'url' => $url,
        'order' => $order,
        'pageLength' => $pageLength,
        'columns' => $columnas->pluck('config')->all(),
    ], fn ($valor) => $valor !== null);
@endphp

@if ($card)
    <div class="card">
        <div class="card-body">
@endif
            <table id="{{ $id }}"
                   {{ $attributes->class(['table table-hover align-middle w-100']) }}
                   data-datatable-config="{{ json_encode($config) }}"
                   @if ($filters) data-filters="{{ $filters }}" @endif>
                <thead>
                    <tr>
                        @foreach ($columnas as $columna)
                            <th{!! $columna['headerClass'] ? ' class="'.e($columna['headerClass']).'"' : '' !!}>{{ $columna['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
            </table>
@if ($card)
        </div>
    </div>
@endif
