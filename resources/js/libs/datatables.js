import $ from './jquery';
import * as bootstrap from 'bootstrap';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import languageEs from 'datatables.net-plugins/i18n/es-ES.mjs';

DataTable.use($);
DataTable.use(bootstrap);

DataTable.util.object.assignDeep(DataTable.defaults, {
    language: languageEs,
    responsive: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false,
});

/**
 * Crea una tabla server-side (Yajra) cuyos datos se obtienen con Axios via POST.
 * `extraParams` es una funcion que devuelve filtros adicionales a enviar.
 */
export function serverTable(selector, { url, columns, order = [[0, 'asc']], extraParams = () => ({}), ...options }) {
    return new DataTable(selector, {
        processing: true,
        serverSide: true,
        order,
        columns,
        ajax: (data, callback) => {
            window.axios
                .post(url, { ...data, ...extraParams() })
                .then(({ data: response }) => callback(response))
                .catch(() => {
                    window.App?.notify('No se pudo cargar la información de la tabla.', 'danger');
                    callback({ draw: data.draw, data: [], recordsTotal: 0, recordsFiltered: 0 });
                });
        },
        ...options,
    });
}

export function reloadTables() {
    DataTable.tables({ api: true }).ajax.reload(null, false);
}

window.DataTable = DataTable;

export default DataTable;
