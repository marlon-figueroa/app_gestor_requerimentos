import { reloadTables } from './datatables';
import { notify, confirmAction, alertMessage } from './alerts';

export { notify };

/**
 * Elimina un recurso via Axios (DELETE) tras confirmar y recarga las DataTables.
 * Uso: <button data-delete-url="..." data-confirm="...">.
 */
export function registerDeleteHandler() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-delete-url]');

        if (!button) {
            return;
        }

        event.preventDefault();

        const confirmado = await confirmAction({
            title: '¿Eliminar registro?',
            text: button.dataset.confirm || '¿Está seguro de eliminar este registro?',
            confirmText: 'Sí, eliminar',
            danger: true,
        });

        if (!confirmado) {
            return;
        }

        button.disabled = true;

        window.axios
            .delete(button.dataset.deleteUrl)
            .then(({ data }) => {
                notify(data.message || 'Registro eliminado correctamente.');
                reloadTables();
            })
            .catch((error) => {
                alertMessage({
                    title: 'No se pudo eliminar',
                    text: error.response?.data?.message || 'Ocurrió un error al eliminar el registro.',
                    type: 'error',
                });
                button.disabled = false;
            });
    });
}
