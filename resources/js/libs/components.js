import $ from './jquery';
import { serverTable, reloadTable } from './datatables';
import { getDatePicker } from './tempus-dominus';

/**
 * Inicializacion de los componentes Blade x-datatable, x-filters y x-action-bar.
 * Las instancias quedan en `tables` (window.App.tables) indexadas por id.
 */
export const tables = {};

const readyCallbacks = {};

function filtersFor(tableEl) {
    if (tableEl.dataset.filters) {
        return document.getElementById(tableEl.dataset.filters);
    }

    return document.querySelector(`form[data-filters-for="${CSS.escape(tableEl.id)}"]`);
}

function formToObject(form) {
    const data = {};

    for (const [key, value] of new FormData(form)) {
        if (key.endsWith('[]')) {
            (data[key.slice(0, -2)] ??= []).push(value);
        } else {
            data[key] = value;
        }
    }

    return data;
}

/**
 * Ejecuta `callback(api)` cuando la tabla del componente x-datatable este lista.
 * Util desde el JS de pagina, que se carga despues de app.js.
 */
export function onTable(id, callback) {
    if (tables[id]) {
        callback(tables[id]);
        return;
    }

    (readyCallbacks[id] ??= []).push(callback);
}

export function initDataTableComponents(container = document) {
    container.querySelectorAll('table[data-datatable-config]').forEach((tableEl) => {
        if (tables[tableEl.id]) {
            return;
        }

        const { url, columns, order, ...options } = JSON.parse(tableEl.dataset.datatableConfig);
        const form = filtersFor(tableEl);

        const api = serverTable(tableEl, {
            url,
            columns,
            order,
            extraParams: () => (form ? formToObject(form) : {}),
            ...options,
        });

        tables[tableEl.id] = api;
        (readyCallbacks[tableEl.id] ?? []).forEach((callback) => callback(api));
        delete readyCallbacks[tableEl.id];

        tableEl.dispatchEvent(new CustomEvent('datatable:ready', { bubbles: true, detail: { id: tableEl.id, table: api } }));
    });
}

function registerFilterHandlers() {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.dataset.filtersFor) {
            return;
        }

        event.preventDefault();
        reloadTable(form.dataset.filtersFor);
    });

    document.addEventListener('reset', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.dataset.filtersFor) {
            return;
        }

        setTimeout(() => {
            $(form).find('select.select2').val(null).trigger('change');
            form.querySelectorAll('input.datetimepicker').forEach((input) => getDatePicker(input)?.dates.clear());
            reloadTable(form.dataset.filtersFor);
        });
    });
}

function registerExportHandler() {
    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-export-table]');

        if (!link) {
            return;
        }

        event.preventDefault();

        const tableEl = document.getElementById(link.dataset.exportTable);
        const form = tableEl ? filtersFor(tableEl) : null;
        const url = new URL(link.href, window.location.origin);

        if (form) {
            for (const [key, value] of new FormData(form)) {
                url.searchParams.append(key, value);
            }
        }

        const search = tables[link.dataset.exportTable]?.search();

        if (search) {
            url.searchParams.set('search', search);
        }

        window.open(url.toString(), '_blank');
    });
}

export function initComponents() {
    initDataTableComponents();
    registerFilterHandlers();
    registerExportHandler();
}
