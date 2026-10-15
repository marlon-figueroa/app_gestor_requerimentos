import Chart from 'chart.js/auto';

const datosEl = document.getElementById('dashboard-requerimientos-datos');
const barrasCanvas = document.getElementById('grafica-barras');
const pastelCanvas = document.getElementById('grafica-pastel');

if (datosEl && barrasCanvas && pastelCanvas) {
    const datos = JSON.parse(datosEl.textContent);
    const resumen = document.getElementById('dashboard-requerimientos-resumen');
    const vacio = document.getElementById('grafica-pastel-vacio');
    const botones = [...document.querySelectorAll('#dashboard-requerimientos [data-periodo]')];

    const colorTema = (variable) => getComputedStyle(document.documentElement).getPropertyValue(variable).trim();

    const barras = new Chart(barrasCanvas, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Requerimientos',
                data: [],
                backgroundColor: '#0d6efd',
                borderRadius: 4,
                maxBarThickness: 36,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true } },
                y: { beginAtZero: true, ticks: { precision: 0 } },
            },
        },
    });

    const pastel = new Chart(pastelCanvas, {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [],
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
            },
        },
    });

    const aplicarTema = () => {
        const texto = colorTema('--bs-body-color');
        const rejilla = colorTema('--bs-border-color');
        const superficie = colorTema('--app-surface') || '#fff';

        barras.options.scales.x.ticks.color = texto;
        barras.options.scales.y.ticks.color = texto;
        barras.options.scales.y.grid.color = rejilla;
        pastel.options.plugins.legend.labels.color = texto;
        pastel.data.datasets[0].borderColor = superficie;
        barras.update();
        pastel.update();
    };

    const pintar = (periodo) => {
        const serie = datos[periodo];

        barras.data.labels = serie.etiquetas;
        barras.data.datasets[0].data = serie.barras;
        barras.update();

        const hayPastel = serie.pastel.length > 0;
        pastelCanvas.parentElement.classList.toggle('d-none', !hayPastel);
        vacio?.classList.toggle('d-none', hayPastel);
        pastel.data.labels = serie.pastel.map((item) => item.etiqueta);
        pastel.data.datasets[0].data = serie.pastel.map((item) => item.valor);
        pastel.data.datasets[0].backgroundColor = serie.pastel.map((item) => item.color);
        pastel.update();

        if (hayPastel) {
            pastel.resize();
        }

        if (resumen) {
            resumen.textContent = serie.resumen;
        }
    };

    botones.forEach((boton) => {
        boton.addEventListener('click', () => {
            botones.forEach((otro) => {
                const activo = otro === boton;
                otro.classList.toggle('active', activo);
                otro.setAttribute('aria-pressed', activo ? 'true' : 'false');
            });
            pintar(boton.dataset.periodo);
        });
    });

    pintar('semanal');
    aplicarTema();
    document.addEventListener('theme:changed', aplicarTema);
}
