export async function initNotificaciones() {
    const menu = document.getElementById('notificaciones-menu');
    if (! menu || ! window.axios) {
        return;
    }

    const badge = document.getElementById('notificaciones-badge');
    const lista = document.getElementById('notificaciones-lista');
    const marcarTodas = document.getElementById('notificaciones-marcar-todas');

    const render = (payload) => {
        const noLeidas = payload.no_leidas || 0;
        badge.textContent = String(noLeidas);
        badge.classList.toggle('d-none', noLeidas === 0);

        if (! payload.items?.length) {
            lista.innerHTML = '<div class="list-group-item text-body-secondary">Sin notificaciones.</div>';

            return;
        }

        lista.innerHTML = payload.items.map((item) => {
            const url = item.data?.url || '#';
            const leida = item.read_at ? 'opacity-75' : 'fw-semibold';

            return `<a href="${url}" class="list-group-item list-group-item-action ${leida}" data-id="${item.id}">
                <div>${item.data?.mensaje || 'Notificación'}</div>
                <div class="text-body-secondary">${item.created_at || ''}</div>
            </a>`;
        }).join('');
    };

    const cargar = async () => {
        try {
            const { data } = await window.axios.get('/notificaciones');
            render(data);
        } catch {
            lista.innerHTML = '<div class="list-group-item text-danger">No se pudieron cargar las notificaciones.</div>';
        }
    };

    marcarTodas?.addEventListener('click', async (e) => {
        e.preventDefault();
        await window.axios.post('/notificaciones/leidas');
        cargar();
    });

    lista.addEventListener('click', async (e) => {
        const link = e.target.closest('[data-id]');
        if (! link) {
            return;
        }
        await window.axios.post(`/notificaciones/${link.dataset.id}/leida`);
    });

    document.getElementById('notificaciones-toggle')?.addEventListener('shown.bs.dropdown', cargar);
    cargar();
}
