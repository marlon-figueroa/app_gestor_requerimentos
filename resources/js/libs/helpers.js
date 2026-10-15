import { reloadTable, reloadTables } from './datatables';
import { notify, confirmAction, alertMessage } from './alerts';

export { notify, confirmAction, alertMessage };

/**
 * Elimina un recurso via Axios (DELETE) tras confirmar. Recarga la DataTable que
 * contiene el boton (o todas) o, si tiene data-redirect, navega a esa URL.
 * Uso: <button data-delete-url="..." data-confirm="..." data-redirect="...">.
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
            .then(async ({ data }) => {
                const redirect = button.dataset.redirect;
                const table = button.closest('table');

                if (!redirect && !reloadTable(table)) {
                    reloadTables();
                }

                await notify(data.message || 'Registro eliminado correctamente.');

                if (redirect) {
                    window.location.assign(redirect);
                }
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
