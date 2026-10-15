<?php

/*
|--------------------------------------------------------------------------
| Menú lateral
|--------------------------------------------------------------------------
|
| Árbol de hasta 3 niveles. Cada elemento admite:
|   label     Texto visible.
|   icon      Nombre de un icono de Feather (https://feathericons.com).
|   route     Nombre de la ruta (solo en elementos sin hijos).
|   active    Patrón de rutas que marcan el elemento como activo (por defecto, route).
|   can       Permiso o lista de permisos; basta con tener uno.
|   children  Submenú. Un elemento con hijos se muestra solo si alguno es visible.
|
*/

return [
    [
        'label' => 'Inicio',
        'icon' => 'home',
        'route' => 'home',
    ],
    [
        'label' => 'Requerimientos',
        'icon' => 'clipboard',
        'children' => [
            ['label' => 'Listado', 'icon' => 'list', 'route' => 'requerimientos.index', 'active' => ['requerimientos.index', 'requerimientos.create', 'requerimientos.show', 'requerimientos.edit', 'requerimientos.casos-uso.*'], 'can' => ['requerimientos.ver', 'requerimientos.revisar']],
            ['label' => 'Bandeja de revisión', 'icon' => 'inbox', 'route' => 'requerimientos.bandeja', 'can' => 'requerimientos.revisar'],
            ['label' => 'Kanban', 'icon' => 'trello', 'route' => 'requerimientos.kanban', 'can' => ['requerimientos.ver', 'requerimientos.revisar']],
            ['label' => 'Calendario', 'icon' => 'calendar', 'route' => 'requerimientos.calendario', 'can' => ['requerimientos.ver', 'requerimientos.revisar']],
        ],
    ],
    [
        'label' => 'Administración',
        'icon' => 'settings',
        'children' => [
            [
                'label' => 'Seguridad',
                'icon' => 'lock',
                'children' => [
                    ['label' => 'Usuarios', 'icon' => 'users', 'route' => 'admin.usuarios.index', 'active' => 'admin.usuarios.*', 'can' => 'usuarios.ver'],
                    ['label' => 'Roles', 'icon' => 'shield', 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'can' => 'roles.ver'],
                    ['label' => 'Permisos', 'icon' => 'key', 'route' => 'admin.permisos.index', 'active' => 'admin.permisos.*', 'can' => 'permisos.ver'],
                ],
            ],
            [
                'label' => 'Auditoría',
                'icon' => 'eye',
                'children' => [
                    ['label' => 'Bitácora', 'icon' => 'book-open', 'route' => 'admin.bitacora.index', 'active' => 'admin.bitacora.*', 'can' => ['bitacora.ver', 'monitoreo.pulse', 'monitoreo.logs']],
                ],
            ],
        ],
    ],
];
