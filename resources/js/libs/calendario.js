import { Calendar } from '@fullcalendar/core';
import esLocale from '@fullcalendar/core/locales/es';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

/**
 * Calendario de vencimientos. Los eventos se piden al servidor por rango visible
 * y opcionalmente por estado (select externo).
 */
export function initCalendario(el, { filtroEstado } = {}) {
    if (!el) {
        return null;
    }

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        locale: esLocale,
        initialView: 'dayGridMonth',
        height: 'auto',
        dayMaxEvents: 4,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth',
        },
        events: (info, success, failure) => {
            window.axios
                .get(el.dataset.url, {
                    params: {
                        start: info.startStr,
                        end: info.endStr,
                        estado: filtroEstado?.value || undefined,
                    },
                })
                .then(({ data }) => success(data))
                .catch((error) => {
                    window.App?.notify('No se pudieron cargar los requerimientos del calendario.', 'danger');
                    failure(error);
                });
        },
        eventDidMount: ({ event, el: eventoEl }) => {
            const { estado, solicitante, solicitud, vencido } = event.extendedProps;
            eventoEl.title = [
                event.title,
                `Estado: ${estado}`,
                `Solicitante: ${solicitante ?? '—'}`,
                `Solicitado: ${solicitud ?? '—'}`,
                vencido ? 'Plazo vencido' : null,
            ].filter(Boolean).join('\n');
        },
    });

    calendar.render();

    filtroEstado?.addEventListener('change', () => calendar.refetchEvents());

    return calendar;
}
