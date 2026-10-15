import { Swal, warning } from './alerts';

/**
 * Pide observaciones para aprobar, rechazar o cancelar. Resuelve el texto, o null si se cancela.
 */
export async function pedirObservaciones({ titulo, requerida, etiqueta, placeholder, boton, mensaje }) {
    const { isConfirmed, value } = await Swal.fire({
        title: titulo,
        input: 'textarea',
        inputLabel: etiqueta || (requerida ? 'Observaciones (obligatorias)' : 'Observaciones (opcionales)'),
        inputPlaceholder: placeholder || (requerida ? 'Indique qué debe corregir el solicitante...' : 'Comentarios para el solicitante...'),
        inputAttributes: { maxlength: '5000', rows: '5' },
        showCancelButton: true,
        confirmButtonText: boton || (requerida ? 'Rechazar' : 'Aprobar'),
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
            actions: 'gap-2',
            confirmButton: `btn ${requerida ? 'btn-danger' : 'btn-success'} px-4`,
            cancelButton: 'btn btn-secondary px-4',
        },
        inputValidator: (texto) => (requerida && !texto.trim()
            ? mensaje || 'Las observaciones son obligatorias para rechazar.'
            : undefined),
    });

    return isConfirmed ? value.trim() : null;
}

/**
 * Formularios con data-observaciones="requerida|opcional" piden el texto antes de enviarse.
 * Con data-bloqueo solo muestran la advertencia y no se envían.
 */
export function initFormulariosFlujo() {
    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || form.dataset.enviado) {
            return;
        }

        if (form.dataset.bloqueo) {
            event.preventDefault();
            warning(form.dataset.bloqueoTitulo || 'Acción no permitida', form.dataset.bloqueo);

            return;
        }

        if (!form.dataset.observaciones) {
            return;
        }

        event.preventDefault();

        const observaciones = await pedirObservaciones({
            titulo: form.dataset.observacionesTitulo,
            requerida: form.dataset.observaciones === 'requerida',
            etiqueta: form.dataset.observacionesEtiqueta,
            placeholder: form.dataset.observacionesPlaceholder,
            boton: form.dataset.observacionesBoton,
            mensaje: form.dataset.observacionesMensaje,
        });

        if (observaciones === null) {
            return;
        }

        form.querySelector('input[name="observaciones"]').value = observaciones;
        form.dataset.enviado = '1';
        form.submit();
    });
}
