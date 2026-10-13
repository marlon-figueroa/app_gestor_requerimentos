import { serverTable } from '../libs/datatables';

const tableEl = document.getElementById('tabla-roles');

if (tableEl) {
    serverTable(tableEl, {
        url: tableEl.dataset.url,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'permissions_count', name: 'permissions_count', searchable: false },
            { data: 'users_count', name: 'users_count', searchable: false },
            { data: 'acciones', orderable: false, searchable: false, className: 'acciones text-end' },
        ],
    });
}
