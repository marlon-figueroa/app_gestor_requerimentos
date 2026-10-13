import $ from '../libs/jquery';
import DataTable, { serverTable, reloadTables } from '../libs/datatables';

const tableEl = document.getElementById('tabla-bitacora');

if (tableEl) {
    const filtros = document.getElementById('filtros-bitacora');
    const getFilters = () => Object.fromEntries(new FormData(filtros));

    const table = serverTable(tableEl, {
        url: tableEl.dataset.url,
        order: [[0, 'desc']],
        extraParams: getFilters,
        columns: [
            { data: 'created_at', name: 'bitacoras.created_at', searchable: false, className: 'text-nowrap' },
            { data: 'usuario_nombre', name: 'usuario.name', orderable: false },
            { data: 'tipo_accion', name: 'tipo_accion' },
            { data: 'modulo', name: 'modulo', defaultContent: '' },
            { data: 'accion', name: 'accion' },
            { data: 'descripcion', name: 'descripcion', defaultContent: '' },
            { data: 'ip_address', name: 'ip_address', defaultContent: '' },
            { data: 'exitoso', name: 'exitoso', searchable: false, className: 'text-center' },
            { data: 'acciones', orderable: false, searchable: false, className: 'acciones text-end' },
        ],
    });

    filtros.addEventListener('submit', (event) => {
        event.preventDefault();
        reloadTables();
    });

    filtros.addEventListener('reset', () => {
        setTimeout(() => {
            $(filtros).find('select.select2').val(null).trigger('change');
            reloadTables();
        });
    });

    document.getElementById('btn-exportar-pdf')?.addEventListener('click', (event) => {
        event.preventDefault();

        const params = new URLSearchParams({ ...getFilters(), search: table.search() });
        window.open(`${event.currentTarget.href}?${params.toString()}`, '_blank');
    });
}

const sesionesEl = document.getElementById('tabla-sesiones-activas');

if (sesionesEl) {
    serverTable(sesionesEl, {
        url: sesionesEl.dataset.url,
        order: [[0, 'desc']],
        columns: [
            { data: 'last_activity', name: 'last_activity' },
            { data: 'usuario_nombre', name: 'user.name', orderable: false },
            { data: 'ip_address', name: 'ip_address' },
            { data: 'cliente', orderable: false, searchable: false },
            { data: 'acciones', orderable: false, searchable: false, className: 'acciones text-end' },
        ],
    });
}

/**
 * Pestañas Registros / Monitoreo (Pulse) / Logs (Log Viewer).
 * Los iframes se cargan al abrir su pestaña y reciben el tema activo del sistema
 * (Pulse y Log Viewer usan la clase "dark" en <html>).
 */
const tabs = document.getElementById('bitacora-tabs');

if (tabs) {
    const botones = [...tabs.querySelectorAll('[data-bs-toggle="tab"]')];
    const exportar = document.getElementById('btn-exportar-pdf');
    const iframes = [...document.querySelectorAll('iframe.panel-embebido')];

    const sincronizarTema = (iframe) => {
        try {
            iframe.contentDocument?.documentElement.classList.toggle('dark', window.App.resolveTheme() === 'dark');
        } catch {
            // El contenido no es del mismo origen: conserva su propio tema.
        }
    };

    iframes.forEach((iframe) => iframe.addEventListener('load', () => sincronizarTema(iframe)));
    document.addEventListener('theme:changed', () => iframes.forEach(sincronizarTema));

    const activar = (boton) => {
        const clave = boton.dataset.tab;
        const iframe = document.querySelector(`#pane-${clave} iframe[data-src]`);

        if (iframe && !iframe.getAttribute('src')) {
            iframe.src = iframe.dataset.src;
        }

        exportar?.classList.toggle('d-none', clave !== 'registros');

        if (clave === 'registros' || clave === 'sesiones') {
            DataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
        }
    };

    const mostrarDesdeHash = () => {
        const boton = botones.find((b) => `#${b.dataset.tab}` === window.location.hash);

        if (boton) {
            window.bootstrap.Tab.getOrCreateInstance(boton).show();
            activar(boton);
        }
    };

    tabs.addEventListener('shown.bs.tab', (event) => {
        activar(event.target);
        window.history.replaceState(null, '', `#${event.target.dataset.tab}`);
    });

    window.addEventListener('hashchange', mostrarDesdeHash);

    const inicial = botones.find((b) => `#${b.dataset.tab}` === window.location.hash) ?? botones.find((b) => b.classList.contains('active'));

    if (inicial) {
        window.bootstrap.Tab.getOrCreateInstance(inicial).show();
        activar(inicial);
    }
}
