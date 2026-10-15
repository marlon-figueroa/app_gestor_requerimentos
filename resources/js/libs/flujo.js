import { Swal } from './alerts';

/**
 * Pide observaciones para aprobar o rechazar. Resuelve el texto, o null si se cancela.
 */
export async function pedirObservaciones({ titulo, requerida }) {
    const { isConfirmed, value } = await Swal.fire({
        title: titulo,
        input: 'textarea',
        inputLabel: requerida ? 'Observaciones (obligatorias)' : 'Observaciones (opcionales)',
        inputPlaceholder: requerida ? 'Indique qué debe corregir el solicitante...' : 'Comentarios para el solicitante...',
        inputAttributes: { maxlength: '5000', rows: '5' },
        showCancelButton: true,
        confirmButtonText: requerida ? 'Rechazar' : 'Aprobar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
            actions: 'gap-2',
            confirmButton: `btn ${requerida ? 'btn-danger' : 'btn-success'} px-4`,
            cancelButton: 'btn btn-secondary px-4',
        },
        inputValidator: (texto) => (requerida && !texto.trim() ? 'Las observaciones son obligatorias para rechazar.' : undefined),
    });

    return isConfirmed ? value.trim() : null;
}

/**
 * Formularios con data-observaciones="requerida|opcional" piden el texto antes de enviarse.
 */
export function initFormulariosFlujo() {
    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.dataset.observaciones || form.dataset.enviado) {
            return;
        }

        event.preventDefault();

        const observaciones = await pedirObservaciones({
            titulo: form.dataset.observacionesTitulo,
            requerida: form.dataset.observaciones === 'requerida',
        });

        if (observaciones === null) {
            return;
        }

        form.querySelector('input[name="observaciones"]').value = observaciones;
        form.dataset.enviado = '1';
        form.submit();
    });
}
