import { TempusDominus, DateTime } from '@eonasdan/tempus-dominus';
import { localization as localizationEs } from '@eonasdan/tempus-dominus/dist/locales/es.js';

const icons = {
    type: 'icons',
    time: 'bi bi-clock',
    date: 'bi bi-calendar-event',
    up: 'bi bi-chevron-up',
    down: 'bi bi-chevron-down',
    previous: 'bi bi-chevron-left',
    next: 'bi bi-chevron-right',
    today: 'bi bi-calendar-check',
    clear: 'bi bi-trash',
    close: 'bi bi-x-lg',
};

const instances = new WeakMap();

export function getDatePicker(input) {
    return instances.get(input) ?? null;
}

/**
 * Inicializa Tempus Dominus en los inputs `.datetimepicker` dentro del contenedor.
 * Opciones por atributos:
 *  - data-time="true"     incluye selector de hora (formato dd/MM/yyyy HH:mm)
 *  - data-format="..."    formato personalizado
 */
export function initDatePickers(container = document) {
    const pickers = [];

    container.querySelectorAll('input.datetimepicker').forEach((input) => {
        if (input.dataset.tdInitialized) {
            return;
        }

        const withTime = input.dataset.time === 'true';

        const picker = new TempusDominus(input, {
            useCurrent: false,
            localization: {
                ...localizationEs,
                locale: 'es-MX',
                format: input.dataset.format || (withTime ? 'dd/MM/yyyy HH:mm' : 'dd/MM/yyyy'),
                hourCycle: 'h23',
            },
            display: {
                icons,
                theme: 'light',
                buttons: { today: true, clear: true, close: true },
                components: {
                    clock: withTime,
                    hours: withTime,
                    minutes: withTime,
                    seconds: false,
                },
            },
        });

        pickers.push(picker);
        instances.set(input, picker);
        input.dataset.tdInitialized = 'true';
    });

    return pickers;
}

export { TempusDominus, DateTime };
