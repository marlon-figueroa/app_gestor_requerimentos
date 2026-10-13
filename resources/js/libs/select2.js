import $ from './jquery';
import select2 from 'select2';

select2(window, $);

const languageEs = {
    errorLoading: () => 'No se pudieron cargar los resultados',
    inputTooLong: ({ input, maximum }) => {
        const remaining = input.length - maximum;
        return `Por favor, elimine ${remaining} car${remaining === 1 ? 'ácter' : 'acteres'}`;
    },
    inputTooShort: ({ input, minimum }) => {
        const remaining = minimum - input.length;
        return `Por favor, introduzca ${remaining} car${remaining === 1 ? 'ácter' : 'acteres'}`;
    },
    loadingMore: () => 'Cargando más resultados…',
    maximumSelected: ({ maximum }) => `Sólo puede seleccionar ${maximum} elemento${maximum === 1 ? '' : 's'}`,
    noResults: () => 'No se encontraron resultados',
    searching: () => 'Buscando…',
    removeAllItems: () => 'Eliminar todos los elementos',
    removeItem: () => 'Eliminar elemento',
    search: () => 'Buscar',
};

$.fn.select2.defaults.set('theme', 'bootstrap-5');
$.fn.select2.defaults.set('width', '100%');
$.fn.select2.defaults.set('language', languageEs);

/**
 * Inicializa Select2 en los elementos `.select2` dentro del contenedor.
 * Opciones por atributos: data-placeholder, data-allow-clear="true".
 */
export function initSelect2(container = document) {
    $(container)
        .find('select.select2')
        .addBack('select.select2')
        .each(function () {
            const $el = $(this);

            if ($el.hasClass('select2-hidden-accessible')) {
                return;
            }

            $el.select2({
                placeholder: $el.data('placeholder') || 'Seleccione una opción',
                allowClear: Boolean($el.data('allow-clear')),
                dropdownParent: $el.closest('.modal').length ? $el.closest('.modal') : $(document.body),
            });
        });
}

export default $;
