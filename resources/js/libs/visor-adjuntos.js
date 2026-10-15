/**
 * Modal visor de adjuntos (partials/visor-adjunto.blade.php). Los disparadores usan:
 *   data-visor data-visor-url="..." data-visor-descargar="..." data-visor-tipo="imagen|pdf" data-visor-nombre="..."
 */
export function initVisorAdjuntos() {
    const modalEl = document.getElementById('visor-adjunto');

    if (!modalEl) {
        return;
    }

    const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
    const titulo = modalEl.querySelector('.modal-title');
    const cuerpo = modalEl.querySelector('[data-visor-cuerpo]');
    const descargar = modalEl.querySelector('[data-visor-descargar]');

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-visor]');

        if (!trigger) {
            return;
        }

        event.preventDefault();

        const { visorUrl, visorDescargar, visorTipo, visorNombre } = trigger.dataset;

        titulo.textContent = visorNombre || 'Archivo adjunto';
        descargar.href = visorDescargar || visorUrl;
        cuerpo.replaceChildren();

        if (visorTipo === 'imagen') {
            const img = document.createElement('img');
            img.src = visorUrl;
            img.alt = visorNombre || '';
            img.className = 'visor-adjunto__imagen';
            cuerpo.appendChild(img);
        } else {
            const frame = document.createElement('iframe');
            frame.src = visorUrl;
            frame.title = visorNombre || 'PDF';
            frame.className = 'visor-adjunto__pdf';
            cuerpo.appendChild(frame);
        }

        modal.show();
    });

    modalEl.addEventListener('hidden.bs.modal', () => cuerpo.replaceChildren());
}
