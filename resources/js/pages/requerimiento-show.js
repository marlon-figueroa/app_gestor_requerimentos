import { Dropzone, opcionesAdjuntos, iconoPdf } from '../libs/adjuntos';
import { initDiagramaPasos } from '../libs/diagrama-requerimiento';
import { initVisorAdjuntos } from '../libs/visor-adjuntos';
import { initFormulariosFlujo } from '../libs/flujo';

initVisorAdjuntos();
initFormulariosFlujo();

const datosDiagrama = document.getElementById('diagrama-pasos-datos');

if (datosDiagrama) {
    initDiagramaPasos(document.getElementById('diagrama-pasos'), JSON.parse(datosDiagrama.textContent));
}

const zone = document.getElementById('adjuntos-subida');

if (zone) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    let errores = 0;

    const dropzone = new Dropzone(zone, opcionesAdjuntos(zone, {
        url: zone.dataset.url,
        paramName: 'archivo',
        parallelUploads: 2,
        headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' },
    }));

    dropzone.on('addedfile', iconoPdf);

    dropzone.on('error', (file, respuesta) => {
        errores += 1;

        const mensaje = typeof respuesta === 'string'
            ? respuesta
            : respuesta?.errors?.archivo?.[0] ?? respuesta?.message ?? 'No se pudo subir el archivo.';

        file.previewElement?.querySelector('[data-dz-errormessage]')?.replaceChildren(document.createTextNode(mensaje));
    });

    dropzone.on('queuecomplete', () => {
        const subidos = dropzone.getFilesWithStatus(Dropzone.SUCCESS).length;

        if (subidos > 0 && errores === 0) {
            window.location.hash = 'adjuntos';
            window.location.reload();
        }
    });
}
