import $ from '../libs/jquery';
import DataTable, { serverTable, reloadTables } from '../libs/datatables';
import { notify } from '../libs/helpers';

const escapeHtml = (value) =>
    String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

const formatValue = (value) => {
    if (value === null || value === undefined || value === '') {
        return '<span class="text-muted">—</span>';
    }

    if (typeof value === 'object') {
        return `<code>${escapeHtml(JSON.stringify(value))}</code>`;
    }

    return escapeHtml(value);
};

const renderCambios = (anteriores, nuevos) => {
    const campos = [...new Set([...Object.keys(anteriores ?? {}), ...Object.keys(nuevos ?? {})])];

    if (!campos.length) {
        return '';
    }

    const filas = campos
        .map(
            (campo) => `
            <tr>
                <th class="text-nowrap">${escapeHtml(campo)}</th>
                <td class="text-danger-emphasis">${formatValue(anteriores?.[campo])}</td>
                <td class="text-success-emphasis">${formatValue(nuevos?.[campo])}</td>
            </tr>`,
        )
        .join('');

    return `
        <h6 class="mt-4"><i class="bi bi-arrow-left-right me-1"></i> Cambios</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead class="text-body-secondary"><tr><th>Campo</th><th>Valor anterior</th><th>Valor nuevo</th></tr></thead>
                <tbody>${filas}</tbody>
            </table>
        </div>`;
};

const renderDetalle = (d) => {
    const datos = [
        ['Fecha', d.fecha],
        ['Usuario', d.usuario],
        ['Tipo de acción', d.tipo_accion],
        ['Acción', d.accion],
        ['Descripción', d.descripcion],
        ['Módulo', d.modulo],
        ['Registro afectado', d.modelo],
        ['Resultado', d.exitoso ? 'Exitoso' : 'Fallido'],
        ['Error', d.error_mensaje],
        ['Código HTTP', d.status_code],
        ['Petición', d.metodo_http ? `${d.metodo_http} /${d.ruta ?? ''}` : null],
        ['IP', d.ip_address],
        ['Navegador / SO / dispositivo', [d.navegador, d.sistema_operativo, d.dispositivo].filter(Boolean).join(' · ')],
        ['Tiempo de ejecución', d.tiempo_ejecucion !== null ? `${d.tiempo_ejecucion} ms` : null],
        ['Etiquetas', d.tags],
    ];

    const filas = datos
        .map(([etiqueta, valor]) => `<tr><th class="text-nowrap w-25">${etiqueta}</th><td>${formatValue(valor)}</td></tr>`)
        .join('');

    const adicionales = d.datos_adicionales
        ? `<h6 class="mt-4"><i class="bi bi-info-circle me-1"></i> Datos adicionales</h6>
           <pre class="bg-body-tertiary p-2 rounded small mb-0">${escapeHtml(JSON.stringify(d.datos_adicionales, null, 2))}</pre>`
        : '';

    return `
        <table class="table table-sm align-middle mb-0"><tbody>${filas}</tbody></table>
        ${renderCambios(d.valores_anteriores, d.valores_nuevos)}
        ${adicionales}
        <p class="small text-muted mt-3 mb-0 text-break">${escapeHtml(d.user_agent)}</p>`;
};

const tableEl = document.getElementById('tabla-bitacora');

if (tableEl) {
    const filtros = document.getElementById('filtros-bitacora');
    const getFilters = () => Object.fromEntries(new FormData(filtros));
    const modalEl = document.getElementById('modal-bitacora');
    const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
    const modalBody = modalEl.querySelector('.modal-body');
    const spinner = modalBody.innerHTML;

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

    tableEl.addEventListener('click', (event) => {
        const button = event.target.closest('[data-bitacora-url]');

        if (!button) {
            return;
        }

        modalBody.innerHTML = spinner;
        modal.show();

        window.axios
            .get(button.dataset.bitacoraUrl)
            .then(({ data }) => {
                modalBody.innerHTML = renderDetalle(data);
            })
            .catch(() => {
                modal.hide();
                notify('No se pudo cargar el detalle del registro.', 'danger');
            });
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

        if (clave === 'registros') {
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
