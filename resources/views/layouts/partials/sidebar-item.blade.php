<li class="nav-item app-sidebar-item" data-nivel="{{ $item['nivel'] }}">
    @if ($item['children'])
        <button type="button"
                @class(['nav-link app-sidebar-branch w-100', 'collapsed' => ! $item['abierto']])
                data-bs-toggle="collapse"
                data-bs-target="#{{ $item['id'] }}"
                aria-controls="{{ $item['id'] }}"
                aria-expanded="{{ $item['abierto'] ? 'true' : 'false' }}"
                title="{{ $item['label'] }}">
            <i data-feather="{{ $item['icon'] }}" class="app-sidebar-icon"></i>
            <span class="text-truncate">{{ $item['label'] }}</span>
            <i data-feather="chevron-right" class="app-sidebar-chevron ms-auto"></i>
        </button>

        <div @class(['collapse', 'show' => $item['abierto']]) id="{{ $item['id'] }}">
            <ul class="nav flex-column app-sidebar-submenu">
                @each('layouts.partials.sidebar-item', $item['children'], 'item')
            </ul>
        </div>
    @else
        <a href="{{ $item['url'] }}"
           @class(['nav-link', 'active' => $item['activo']])
           title="{{ $item['label'] }}"
           @if ($item['activo']) aria-current="page" @endif>
            <i data-feather="{{ $item['icon'] }}" class="app-sidebar-icon"></i>
            <span class="text-truncate">{{ $item['label'] }}</span>
        </a>
    @endif
</li>
