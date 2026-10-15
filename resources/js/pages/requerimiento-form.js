import { Dropzone, opcionesAdjuntos, iconoPdf } from '../libs/adjuntos';

const zone = document.getElementById('adjuntos-dropzone');
const input = document.getElementById('adjuntos');

if (zone && input) {
    const form = zone.closest('form');

    const dropzone = new Dropzone(zone, opcionesAdjuntos(zone, {
        url: form?.getAttribute('action') || '/',
        autoProcessQueue: false,
        autoQueue: false,
    }));

    const sincronizar = () => {
        const transfer = new DataTransfer();

        dropzone.getAcceptedFiles().forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    };

    dropzone.on('addedfile', (file) => {
        iconoPdf(file);
        setTimeout(sincronizar);
    });
    dropzone.on('removedfile', sincronizar);
    dropzone.on('error', sincronizar);

    form?.addEventListener('submit', (event) => {
        sincronizar();

        if (dropzone.getRejectedFiles().length > 0) {
            event.preventDefault();
            zone.scrollIntoView({ block: 'center' });
        }
    });
}
