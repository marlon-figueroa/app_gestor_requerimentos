import Swal from 'sweetalert2/dist/sweetalert2.esm.js';

/**
 * Mensajes del sistema con SweetAlert2 (los estilos se cargan desde app.scss
 * y los colores siguen el tema claro/oscuro via variables CSS en _theme.scss).
 */
const ICONS = {
    success: 'success',
    error: 'error',
    danger: 'error',
    warning: 'warning',
    info: 'info',
    question: 'question',
    status: 'success',
};

const Dialog = Swal.mixin({
    buttonsStyling: false,
    reverseButtons: true,
    focusCancel: false,
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar',
    denyButtonText: 'No',
    customClass: {
        actions: 'gap-2',
        confirmButton: 'btn btn-primary px-4',
        denyButton: 'btn btn-outline-secondary px-4',
        cancelButton: 'btn btn-secondary px-4',
    },
});

const icon = (type) => ICONS[type] ?? 'info';

const TITULOS = {
    success: 'Éxito',
    error: 'Error',
    danger: 'Error',
    warning: 'Aviso',
    info: 'Información',
};

function tituloPorTipo(type) {
    return TITULOS[type] ?? TITULOS.info;
}

/**
 * Notificacion modal con titulo segun el tipo y el mensaje en el cuerpo.
 * notify('Guardado', 'success' | 'error' | 'danger' | 'warning' | 'info')
 */
export function notify(message, type = 'success') {
    return alertMessage({ title: tituloPorTipo(type), text: message, type });
}

/**
 * Mensaje modal con boton Aceptar.
 * alertMessage({ title, text, html, type })
 */
export function alertMessage({ title, text, html, type = 'info', ...options } = {}) {
    return Dialog.fire({ icon: icon(type), title, text, html, ...options });
}

export const success = (title, text) => alertMessage({ title, text, type: 'success' });
export const error = (title, text) => alertMessage({ title, text, type: 'error' });
export const warning = (title, text) => alertMessage({ title, text, type: 'warning' });
export const info = (title, text) => alertMessage({ title, text, type: 'info' });

/**
 * Confirmacion; resuelve true si el usuario acepta.
 * confirmAction({ title, text, confirmText, danger })
 */
export async function confirmAction({
    title = '¿Está seguro?',
    text = '',
    confirmText = 'Sí, continuar',
    cancelText = 'Cancelar',
    type = 'warning',
    danger = false,
    ...options
} = {}) {
    const { isConfirmed } = await Dialog.fire({
        icon: icon(type),
        title,
        text,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        customClass: {
            actions: 'gap-2',
            confirmButton: `btn ${danger ? 'btn-danger' : 'btn-primary'} px-4`,
            cancelButton: 'btn btn-secondary px-4',
        },
        ...options,
    });

    return isConfirmed;
}

/**
 * Muestra los mensajes flash de sesion que el servidor deja en
 * <script type="application/json" id="flash-messages"> (partials/alerts.blade.php).
 */
function showFlashMessages() {
    const element = document.getElementById('flash-messages');

    if (!element) {
        return;
    }

    let messages = [];

    try {
        messages = JSON.parse(element.textContent);
    } catch {
        return;
    }

    if (!Array.isArray(messages) || messages.length === 0) {
        return;
    }

    // SweetAlert2 muestra un popup a la vez: se agrupan los mensajes y se usa el tipo mas grave.
    const SEVERIDAD = ['error', 'danger', 'warning', 'info', 'success'];
    const tipo = SEVERIDAD.find((t) => messages.some((m) => m.type === t)) ?? 'info';

    const contenido = document.createElement('div');
    messages.forEach(({ message }) => {
        const linea = document.createElement('div');
        linea.textContent = message;
        contenido.appendChild(linea);
    });

    const title = tituloPorTipo(tipo);

    if (messages.length === 1) {
        alertMessage({ title, text: messages[0].message, type: tipo });
    } else {
        alertMessage({ title, html: contenido, type: tipo });
    }
}

/**
 * Confirmacion declarativa para formularios y enlaces:
 *   <form data-confirm="¿Enviar?" data-confirm-title="..." data-confirm-danger>
 *   <a href="..." data-confirm="¿Salir?">
 * (los botones con data-delete-url los gestiona registerDeleteHandler).
 */
function registerConfirmHandlers() {
    const opciones = (element) => ({
        title: element.dataset.confirmTitle || '¿Está seguro?',
        text: element.dataset.confirm,
        confirmText: element.dataset.confirmButton || 'Sí, continuar',
        danger: 'confirmDanger' in element.dataset,
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.matches('[data-confirm]') || form.dataset.confirmed) {
            return;
        }

        event.preventDefault();

        if (await confirmAction(opciones(form))) {
            form.dataset.confirmed = '1';
            form.requestSubmit(event.submitter ?? undefined);
        }
    });

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('a[data-confirm]');

        if (!link) {
            return;
        }

        event.preventDefault();

        if (await confirmAction(opciones(link))) {
            window.location.href = link.href;
        }
    });
}

export function initAlerts() {
    registerConfirmHandlers();
    showFlashMessages();
}

export { Swal };
