import Dropzone from 'dropzone';

Dropzone.autoDiscover = false;

export const TIPOS_ADJUNTO = 'image/png,image/jpeg,application/pdf';

/**
 * Opciones comunes de Dropzone para adjuntos de requerimientos (PNG, JPG o PDF).
 * `data-max-files` y `data-max-filesize` (MB) se leen del contenedor.
 */
export function opcionesAdjuntos(zone, options = {}) {
    return {
        acceptedFiles: TIPOS_ADJUNTO,
        maxFiles: Number(zone.dataset.maxFiles) || null,
        maxFilesize: Number(zone.dataset.maxFilesize) || 10,
        addRemoveLinks: true,
        hiddenInputContainer: zone,
        thumbnailWidth: 120,
        thumbnailHeight: 120,
        dictDefaultMessage: '<span class="photo-dropzone-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-up"></i></span><span>Arrastra los archivos aquí o haz clic para seleccionarlos</span>',
        dictFallbackMessage: 'Tu navegador no permite arrastrar archivos.',
        dictFileTooBig: 'El archivo pesa {{filesize}} MB. El máximo es {{maxFilesize}} MB.',
        dictInvalidFileType: 'Solo se permiten archivos PNG, JPG o PDF.',
        dictResponseError: 'El servidor respondió con el código {{statusCode}}.',
        dictCancelUpload: 'Cancelar',
        dictUploadCanceled: 'Se canceló la carga.',
        dictCancelUploadConfirmation: '¿Cancelar esta carga?',
        dictRemoveFile: 'Quitar',
        dictMaxFilesExceeded: 'No se pueden agregar más archivos.',
        ...options,
    };
}

/**
 * Los PDF no tienen miniatura: se muestra un icono en su lugar.
 */
export function iconoPdf(file) {
    if (file.type !== 'application/pdf' || !file.previewElement) {
        return;
    }

    const imagen = file.previewElement.querySelector('.dz-image');

    if (imagen) {
        imagen.innerHTML = '<i class="bi bi-file-earmark-pdf text-danger dz-pdf-icon" aria-hidden="true"></i>';
    }
}

export { Dropzone };
