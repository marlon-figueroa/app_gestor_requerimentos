import Sortable from 'sortablejs';
import { Swal, alertMessage } from './alerts';
import { pedirObservaciones } from './flujo';

/**
 * Tablero kanban de requerimientos. Cada tarjeta declara en data-destinos los estados
 * a los que el usuario puede moverla; el servidor vuelve a validar la transición.
 */
export function initKanban(board) {
    if (!board) {
        return;
    }

    const transiciones = JSON.parse(board.dataset.transiciones || '{}');
    const urlFlujo = board.dataset.urlFlujo;
    const listas = board.querySelectorAll('.kanban__lista');

    const destinosDe = (tarjeta) => (tarjeta.dataset.destinos || '').split(',').filter(Boolean);

    const marcarDestinos = (tarjeta) => {
        const destinos = destinosDe(tarjeta);

        listas.forEach((lista) => {
            if (lista.dataset.estado === tarjeta.dataset.estado) {
                return;
            }

            lista.classList.add(destinos.includes(lista.dataset.estado) ? 'kanban__destino-valido' : 'kanban__destino-invalido');
        });
    };

    const limpiarDestinos = () => {
        listas.forEach((lista) => lista.classList.remove('kanban__destino-valido', 'kanban__destino-invalido'));
    };

    const actualizarContadores = () => {
        listas.forEach((lista) => {
            const contador = lista.closest('.kanban__columna')?.querySelector('[data-contador]');

            if (contador) {
                contador.textContent = lista.querySelectorAll('.kanban__tarjeta').length;
            }
        });
    };

    const regresar = ({ item, from, oldIndex }) => {
        from.insertBefore(item, from.children[oldIndex] ?? null);
        actualizarContadores();
    };

    const mover = async (evento) => {
        const { item, from, to } = evento;
        const origen = from.dataset.estado;
        const destino = to.dataset.estado;
        const accion = transiciones[origen]?.[destino];

        if (!accion) {
            regresar(evento);
            return;
        }

        let observaciones = null;

        if (accion === 'aprobar' || accion === 'rechazar') {
            observaciones = await pedirObservaciones({
                titulo: `${accion === 'aprobar' ? 'Aprobar' : 'Rechazar'} ${item.dataset.codigo}`,
                requerida: accion === 'rechazar',
            });

            if (observaciones === null) {
                regresar(evento);
                return;
            }
        }

        const url = urlFlujo.replace('__ID__', item.dataset.id).replace('__ACCION__', accion);

        try {
            const { data } = await window.axios.post(url, { observaciones });

            await Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1400,
            });

            window.location.reload();
        } catch (error) {
            regresar(evento);
            alertMessage({
                title: 'No se pudo cambiar el estado',
                text: error.response?.data?.message || 'Ocurrió un error al actualizar el requerimiento.',
                type: 'error',
            });
        }
    };

    listas.forEach((lista) => {
        Sortable.create(lista, {
            group: {
                name: 'kanban',
                pull: (to, from, tarjeta) => tarjeta.dataset.arrastrable === '1',
                put: (to, from, tarjeta) => destinosDe(tarjeta).includes(to.el.dataset.estado),
            },
            sort: false,
            animation: 150,
            draggable: '.kanban__tarjeta[data-arrastrable="1"]',
            filter: 'a, button, form',
            preventOnFilter: false,
            ghostClass: 'kanban__fantasma',
            onStart: ({ item }) => marcarDestinos(item),
            onEnd: (evento) => {
                limpiarDestinos();

                if (evento.from !== evento.to) {
                    actualizarContadores();
                    mover(evento);
                }
            },
        });
    });
}
