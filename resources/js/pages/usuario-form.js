import Dropzone from 'dropzone';

const zone = document.getElementById('photo-dropzone');
const input = document.getElementById('photo');

if (zone && input) {
    const form = zone.closest('form');
    const existingUrl = zone.dataset.existingPhoto || '';

    const dropzone = new Dropzone(zone, {
        url: form?.getAttribute('action') || '/',
        paramName: 'photo',
        autoProcessQueue: false,
        autoQueue: false,
        uploadMultiple: false,
        maxFilesize: 2,
        acceptedFiles: 'image/jpeg,image/png,image/webp,image/gif',
        addRemoveLinks: true,
        hiddenInputContainer: zone,
        thumbnailMethod: 'crop',
        dictDefaultMessage: '<span class="photo-dropzone-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-up"></i></span><span>Arrastra la foto aquí o haz clic para seleccionarla</span>',
        dictFallbackMessage: 'Tu navegador no permite arrastrar archivos.',
        dictFallbackText: 'Usa el campo de abajo para elegir la foto.',
        dictFileTooBig: 'La imagen pesa {{filesize}} MB. El máximo es {{maxFilesize}} MB.',
        dictInvalidFileType: 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.',
        dictResponseError: 'El servidor respondió con el código {{statusCode}}.',
        dictCancelUpload: 'Cancelar',
        dictUploadCanceled: 'Se canceló la carga.',
        dictCancelUploadConfirmation: '¿Cancelar esta carga?',
        dictRemoveFile: 'Quitar',
        dictMaxFilesExceeded: 'Solo puedes elegir una foto.',
        accept(file, done) {
            this.files
                .filter((item) => item !== file)
                .forEach((item) => this.removeFile(item));
            done();
            syncPhoto(this, input);
        },
    });

    const showExisting = () => {
        if (!existingUrl || dropzone.files.some((file) => file.existing)) {
            return;
        }

        const mock = {
            name: 'Foto actual',
            size: 0,
            accepted: true,
            existing: true,
            status: Dropzone.SUCCESS,
        };

        dropzone.displayExistingFile(mock, existingUrl, null, null, false);
        mock.previewElement?.classList.add('dz-existing');
        dropzone.files.push(mock);
    };

    dropzone.on('removedfile', (file) => {
        syncPhoto(dropzone, input);

        if (!file.existing && dropzone.files.length === 0) {
            showExisting();
        }
    });

    dropzone.on('error', () => syncPhoto(dropzone, input));

    showExisting();

    form?.addEventListener('submit', (event) => {
        syncPhoto(dropzone, input);

        if (dropzone.getRejectedFiles().length > 0) {
            event.preventDefault();
            zone.scrollIntoView({ block: 'center' });
        }
    });
}

function syncPhoto(dropzone, input) {
    const transfer = new DataTransfer();

    dropzone.getAcceptedFiles().forEach((file) => {
        if (file instanceof File && !file.existing) {
            transfer.items.add(file);
        }
    });

    input.files = transfer.files;
}
