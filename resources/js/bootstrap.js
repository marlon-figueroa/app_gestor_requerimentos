import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

/**
 * Axios queda disponible globalmente y envia el token CSRF en cada peticion,
 * ademas de la cabecera X-Requested-With para que Laravel detecte AJAX.
 */
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfToken = document.head.querySelector('meta[name="csrf-token"]');

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}
