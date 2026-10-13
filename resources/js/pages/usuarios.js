import $ from '../libs/jquery';
import { serverTable, reloadTables } from '../libs/datatables';

const tableEl = document.getElementById('tabla-usuarios');

if (tableEl) {
    const filtros = document.getElementById('filtros-usuarios');
    const getFilters = () => Object.fromEntries(new FormData(filtros));

    const table = serverTable(tableEl, {
        url: tableEl.dataset.url,
        order: [[0, 'desc']],
        extraParams: getFilters,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'roles', name: 'roles.name', orderable: false },
            { data: 'created_at', name: 'created_at', searchable: false },
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
