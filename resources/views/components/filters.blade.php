@props([
    'id',
    'target',
    'title' => 'Filtros',
    'buttonsClass' => 'col-md-2',
])

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-funnel me-1"></i> {{ $title }}</div>
    <div class="card-body">
        <form id="{{ $id }}" {{ $attributes->class(['row g-3 align-items-end']) }} data-filters-for="{{ $target }}" autocomplete="off">
            {{ $slot }}

            <div class="{{ $buttonsClass }} d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill" title="Filtrar">
                    <i class="bi bi-search"></i>
                </button>
                <button type="reset" class="btn btn-outline-secondary flex-fill" title="Limpiar">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        </form>
    </div>
</div>
