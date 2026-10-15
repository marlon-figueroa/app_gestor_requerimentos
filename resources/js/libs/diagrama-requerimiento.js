import { dia, shapes } from '@joint/core';

const ANCHO = 250;
const ALTO = 168;
const SEPARACION_X = 64;
const SEPARACION_Y = 72;
const MARGEN = 8;

const Paso = dia.Element.define('app.Paso', {
    size: { width: ANCHO, height: ALTO },
    attrs: {
        body: {
            width: 'calc(w)',
            height: 'calc(h)',
            rx: 12,
            ry: 12,
            strokeWidth: 1,
        },
        punto: { cx: 22, cy: 30, r: 5 },
        etapa: { x: 36, y: 35, fontSize: 14, fontWeight: 700, fontFamily: 'Nunito, sans-serif' },
        actual: {
            x: ANCHO - 16,
            y: 34,
            fontSize: 11,
            fontWeight: 700,
            fontFamily: 'Nunito, sans-serif',
            textAnchor: 'end',
        },
        accion: { x: 16, y: 62, fontSize: 12, fontFamily: 'Nunito, sans-serif' },
        actor: { x: 16, y: 90, fontSize: 13, fontWeight: 700, fontFamily: 'Nunito, sans-serif' },
        rol: { x: 16, y: 112, fontSize: 12, fontWeight: 600, fontFamily: 'Nunito, sans-serif' },
        fecha: { x: 16, y: 134, fontSize: 11, fontFamily: 'Nunito, sans-serif' },
        nota: { x: 16, y: 154, fontSize: 11, fontFamily: 'Nunito, sans-serif' },
    },
}, {
    markup: [
        { tagName: 'rect', selector: 'body' },
        { tagName: 'circle', selector: 'punto' },
        { tagName: 'text', selector: 'etapa' },
        { tagName: 'text', selector: 'actual' },
        { tagName: 'text', selector: 'accion' },
        { tagName: 'text', selector: 'actor' },
        { tagName: 'text', selector: 'rol' },
        { tagName: 'text', selector: 'fecha' },
        { tagName: 'text', selector: 'nota' },
        { tagName: 'title', selector: 'pista' },
    ],
});

const namespace = { ...shapes, app: { Paso } };

function coloresTema() {
    const oscuro = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    return {
        fondo: oscuro ? '#212529' : '#ffffff',
        borde: oscuro ? '#495057' : '#dee2e6',
        texto: oscuro ? '#f8f9fa' : '#212529',
        muted: oscuro ? '#adb5bd' : '#6c757d',
        enlace: oscuro ? '#868e96' : '#94a3b8',
        solicitante: '#0d6efd',
        gestor: '#6f42c1',
    };
}

function recortar(texto, maximo) {
    if (!texto) {
        return '';
    }

    return texto.length > maximo ? `${texto.slice(0, maximo - 1)}…` : texto;
}

function textoRol(paso) {
    if (paso.rol === 'Gestor' && paso.mismoSolicitante) {
        return 'Gestor · también es el solicitante';
    }

    return paso.rol;
}

function colorRol(paso, colores) {
    return paso.rol === 'Gestor' ? colores.gestor : colores.solicitante;
}

export function initDiagramaPasos(contenedor, pasos) {
    if (!contenedor || !Array.isArray(pasos) || pasos.length === 0) {
        return;
    }

    let papel = null;
    let ultimoAncho = 0;

    const dibujar = () => {
        const anchoDisponible = contenedor.clientWidth;

        if (!anchoDisponible) {
            return;
        }

        papel?.remove();
        contenedor.replaceChildren();

        const columnas = Math.max(1, Math.floor((anchoDisponible - MARGEN * 2 + SEPARACION_X) / (ANCHO + SEPARACION_X)));
        const filas = Math.ceil(pasos.length / columnas);
        const colores = coloresTema();
        const grafico = new dia.Graph({}, { cellNamespace: namespace });
        const elementos = pasos.map((paso, indice) => {
            const columna = indice % columnas;
            const fila = Math.floor(indice / columnas);
            const elemento = new Paso({
                position: {
                    x: MARGEN + columna * (ANCHO + SEPARACION_X),
                    y: MARGEN + fila * (ALTO + SEPARACION_Y),
                },
                attrs: {
                    body: {
                        fill: colores.fondo,
                        stroke: paso.actual ? paso.color : colores.borde,
                        strokeWidth: paso.actual ? 2.5 : 1,
                    },
                    punto: { fill: paso.color },
                    etapa: { text: recortar(paso.etapa, 18), fill: colores.texto },
                    actual: { text: paso.actual ? 'Actual' : '', fill: paso.color },
                    accion: { text: recortar(paso.accion, 34), fill: colores.muted },
                    actor: { text: recortar(paso.actor, 28), fill: colores.texto },
                    rol: { text: textoRol(paso), fill: colorRol(paso, colores) },
                    fecha: { text: paso.fecha ?? '', fill: colores.muted },
                    nota: {
                        text: recortar(paso.observaciones, 36),
                        fill: paso.rechazo ? '#dc3545' : colores.muted,
                    },
                    pista: {
                        text: [paso.actor, textoRol(paso), paso.accion, paso.observaciones].filter(Boolean).join('. '),
                    },
                },
            });

            elemento.addTo(grafico);

            return elemento;
        });

        elementos.slice(0, -1).forEach((origen, indice) => {
            const destino = elementos[indice + 1];
            const mismaFila = Math.floor(indice / columnas) === Math.floor((indice + 1) / columnas);

            new shapes.standard.Link({
                source: { id: origen.id, anchor: { name: mismaFila ? 'right' : 'bottom' } },
                target: { id: destino.id, anchor: { name: mismaFila ? 'left' : 'top' } },
                router: { name: 'manhattan', args: { padding: 14, step: 8 } },
                connector: { name: 'rounded', args: { radius: 8 } },
                attrs: {
                    line: {
                        stroke: pasos[indice + 1].rechazo ? '#dc3545' : colores.enlace,
                        strokeWidth: 2,
                        targetMarker: { type: 'path', d: 'M 8 -4 0 0 8 4 z', fill: pasos[indice + 1].rechazo ? '#dc3545' : colores.enlace },
                    },
                },
            }).addTo(grafico);
        });

        papel = new dia.Paper({
            el: contenedor,
            model: grafico,
            width: Math.max(anchoDisponible, columnas * (ANCHO + SEPARACION_X) + MARGEN),
            height: filas * (ALTO + SEPARACION_Y) + MARGEN,
            async: false,
            frozen: false,
            interactive: false,
            cellViewNamespace: namespace,
            background: { color: 'transparent' },
        });

        ultimoAncho = anchoDisponible;
    };

    dibujar();

    const observador = new ResizeObserver(() => {
        const ancho = contenedor.clientWidth;

        if (!ancho || ancho === ultimoAncho) {
            return;
        }

        dibujar();
    });

    observador.observe(contenedor);
    document.addEventListener('theme:changed', () => {
        ultimoAncho = 0;
        dibujar();
    });
}
