import { serverTable } from '../libs/datatables';

const tableEl = document.getElementById('tabla-permisos');

if (tableEl) {
    serverTable(tableEl, {
        url: tableEl.dataset.url,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'roles', name: 'roles.name', orderable: false },
            { data: 'acciones', orderable: false, searchable: false, className: 'acciones text-end' },
        ],
    });
}
