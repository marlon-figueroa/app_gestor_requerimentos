import { initCalendario } from '../libs/calendario';

initCalendario(document.getElementById('calendario'), {
    filtroEstado: document.getElementById('calendario-estado'),
    filtroProyecto: document.getElementById('calendario-proyecto'),
});
