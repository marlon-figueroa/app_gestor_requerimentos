const subnav = document.querySelector('.perfil-subnav');
const secciones = ['informacion', 'seguridad', 'proyectos', 'requerimientos'];

if (window.location.hash) {
    const destino = document.querySelector(window.location.hash);
    destino?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

if (subnav) {
    subnav.querySelectorAll('a[href^="#"]').forEach((enlace) => {
        enlace.addEventListener('click', (event) => {
            const id = enlace.getAttribute('href');
            const destino = id ? document.querySelector(id) : null;
            if (!destino) {
                return;
            }

            event.preventDefault();
            destino.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.replaceState(null, '', id);
            subnav.querySelectorAll('.nav-link').forEach((pill) => pill.classList.remove('active'));
            enlace.classList.add('active');
        });
    });

    const observador = new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (!visible?.target?.id) {
                return;
            }

            const activo = subnav.querySelector(`a[href="#${visible.target.id}"]`);
            if (!activo) {
                return;
            }

            subnav.querySelectorAll('.nav-link').forEach((pill) => pill.classList.remove('active'));
            activo.classList.add('active');
        },
        { rootMargin: '-30% 0px -55% 0px', threshold: [0, 0.25, 0.5] },
    );

    secciones.forEach((id) => {
        const nodo = document.getElementById(id);
        if (nodo) {
            observador.observe(nodo);
        }
    });
}
