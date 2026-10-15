import { dia, shapes } from '@joint/core';

const FUENTE = 'Nunito, sans-serif';
const ESTADO = { width: 128, height: 56 };
const INICIAL = { width: 24, height: 24 };
const FINAL = { width: 28, height: 28 };
const MARGEN = 12;
const ALTURA_BUCLE = 30;
const APERTURA_BUCLE = 18;
const SEPARACION_PARALELA = 26;
const ESCALA_MINIMA_HORIZONTAL = 0.75;
const INICIO = 'inicio';
const FIN = 'fin';

const UBICACION = {
    [INICIO]: [0, 0],
    borrador: [1, 0],
    en_revision: [2, 0],
    rechazado: [2, 1],
    aprobado: [3, 0],
    en_desarrollo: [4, 0],
    finalizado: [5, 0],
    [FIN]: [6, 0],
};

const ORIENTACIONES = {
    horizontal: {
        principal: 'x',
        inicioPrincipal: 'left',
        finPrincipal: 'right',
        inicioCruzado: 'top',
        finCruzado: 'bottom',
        hueco: 88,
        huecoCruzado: 72,
        margenCruzado: 64,
    },
    vertical: {
        principal: 'y',
        inicioPrincipal: 'top',
        finPrincipal: 'bottom',
        inicioCruzado: 'left',
        finCruzado: 'right',
        hueco: 60,
        huecoCruzado: 128,
        margenCruzado: 120,
    },
};

const Inicial = dia.Element.define('uml.Inicial', {
    size: INICIAL,
    attrs: {
        body: { cx: INICIAL.width / 2, cy: INICIAL.height / 2, r: INICIAL.width / 2 - 1 },
    },
}, {
    markup: [
        { tagName: 'circle', selector: 'body' },
        { tagName: 'title', selector: 'pista' },
    ],
});

const Final = dia.Element.define('uml.Final', {
    size: FINAL,
    attrs: {
        exterior: { cx: FINAL.width / 2, cy: FINAL.height / 2, r: FINAL.width / 2 - 1, fill: 'none', strokeWidth: 2 },
        interior: { cx: FINAL.width / 2, cy: FINAL.height / 2, r: FINAL.width / 2 - 6 },
    },
}, {
    markup: [
        { tagName: 'circle', selector: 'exterior' },
        { tagName: 'circle', selector: 'interior' },
        { tagName: 'title', selector: 'pista' },
    ],
});

const Estado = dia.Element.define('uml.Estado', {
    size: ESTADO,
    attrs: {
        body: { width: 'calc(w)', height: 'calc(h)', rx: 14, ry: 14 },
        estereotipo: {
            x: ESTADO.width / 2,
            y: 16,
            textAnchor: 'middle',
            textVerticalAnchor: 'middle',
            fontSize: 10,
            fontStyle: 'italic',
            fontFamily: FUENTE,
        },
        nombre: {
            x: ESTADO.width / 2,
            textAnchor: 'middle',
            textVerticalAnchor: 'middle',
            fontSize: 13,
            fontWeight: 700,
            fontFamily: FUENTE,
        },
    },
}, {
    markup: [
        { tagName: 'rect', selector: 'body' },
        { tagName: 'text', selector: 'estereotipo' },
        { tagName: 'text', selector: 'nombre' },
        { tagName: 'title', selector: 'pista' },
    ],
});

const namespace = { ...shapes, uml: { Inicial, Final, Estado } };

function coloresTema() {
    const oscuro = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    return {
        fondo: oscuro ? '#212529' : '#ffffff',
        borde: oscuro ? '#495057' : '#ced4da',
        texto: oscuro ? '#f8f9fa' : '#212529',
        muted: oscuro ? '#adb5bd' : '#6c757d',
        linea: oscuro ? '#ced4da' : '#495057',
        rechazo: '#dc3545',
    };
}

function tamano(id) {
    if (id === INICIO) {
        return INICIAL;
    }

    return id === FIN ? FINAL : ESTADO;
}

function acumular(medidas, hueco, inicio) {
    const inicios = [];
    let posicion = inicio;

    for (let indice = 0; indice < medidas.length; indice += 1) {
        inicios[indice] = posicion;
        posicion += (medidas[indice] ?? 0) + hueco;
    }

    return { inicios, total: posicion - hueco };
}

function calcularDisposicion(ids, orientacion) {
    const o = ORIENTACIONES[orientacion];
    const ejes = (medida) => (o.principal === 'x' ? [medida.width, medida.height] : [medida.height, medida.width]);
    const nodos = ids.map((id, indice) => {
        const [principal, cruzado] = UBICACION[id] ?? [indice, 2];
        const [largo, ancho] = ejes(tamano(id));

        return { id, principal, cruzado, largo, ancho };
    });

    const largos = [];
    const anchos = [];

    nodos.forEach((nodo) => {
        largos[nodo.principal] = Math.max(largos[nodo.principal] ?? 0, nodo.largo);
        anchos[nodo.cruzado] = Math.max(anchos[nodo.cruzado] ?? 0, nodo.ancho);
    });

    const filasPrincipales = acumular(largos, o.hueco, MARGEN);
    const filasCruzadas = acumular(anchos, o.huecoCruzado, o.margenCruzado);
    const ubicaciones = new Map();

    nodos.forEach((nodo) => {
        const p = filasPrincipales.inicios[nodo.principal] + (largos[nodo.principal] - nodo.largo) / 2;
        const c = filasCruzadas.inicios[nodo.cruzado] + (anchos[nodo.cruzado] - nodo.ancho) / 2;

        ubicaciones.set(nodo.id, { ...nodo, p, c });
    });

    const totalPrincipal = filasPrincipales.total + MARGEN;
    const totalCruzado = filasCruzadas.total + MARGEN;

    return {
        o,
        ubicaciones,
        ancho: o.principal === 'x' ? totalPrincipal : totalCruzado,
        alto: o.principal === 'x' ? totalCruzado : totalPrincipal,
    };
}

function vector(o, p, c) {
    return o.principal === 'x' ? { x: p, y: c } : { x: c, y: p };
}

function desplazar(o, distancia) {
    return o.principal === 'x' ? { dx: distancia } : { dy: distancia };
}

function alinear({ x, y }) {
    return {
        textAnchor: x < 0 ? 'end' : x > 0 ? 'start' : 'middle',
        textVerticalAnchor: y < 0 ? 'bottom' : y > 0 ? 'top' : 'middle',
    };
}

function geometria(transicion, { o, ubicaciones }) {
    const origen = ubicaciones.get(transicion.origen);
    const destino = ubicaciones.get(transicion.destino);

    if (transicion.origen === transicion.destino) {
        const centro = origen.p + origen.largo / 2;

        return {
            source: { anchor: { name: o.inicioCruzado, args: desplazar(o, -APERTURA_BUCLE) } },
            target: { anchor: { name: o.inicioCruzado, args: desplazar(o, APERTURA_BUCLE) } },
            vertices: [
                vector(o, centro - APERTURA_BUCLE, origen.c - ALTURA_BUCLE),
                vector(o, centro + APERTURA_BUCLE, origen.c - ALTURA_BUCLE),
            ],
            desfaseEtiqueta: vector(o, 0, -6),
        };
    }

    if (origen.cruzado === destino.cruzado) {
        const avanza = destino.principal > origen.principal;

        return {
            source: { anchor: { name: avanza ? o.finPrincipal : o.inicioPrincipal } },
            target: { anchor: { name: avanza ? o.inicioPrincipal : o.finPrincipal } },
            vertices: [],
            desfaseEtiqueta: vector(o, 0, -6),
        };
    }

    const baja = destino.cruzado > origen.cruzado;
    const signo = baja ? -1 : 1;

    return {
        source: { anchor: { name: baja ? o.finCruzado : o.inicioCruzado, args: desplazar(o, signo * SEPARACION_PARALELA) } },
        target: { anchor: { name: baja ? o.inicioCruzado : o.finCruzado, args: desplazar(o, signo * SEPARACION_PARALELA) } },
        vertices: [],
        desfaseEtiqueta: vector(o, signo * 8, 0),
    };
}

function crearNodo(id, estado, posicion, alcanzado, colores) {
    if (id === INICIO) {
        return new Inicial({
            position: posicion,
            attrs: {
                body: { fill: alcanzado ? colores.texto : colores.borde },
                pista: { text: 'Estado inicial' },
            },
        });
    }

    if (id === FIN) {
        const color = alcanzado ? colores.texto : colores.borde;

        return new Final({
            position: posicion,
            attrs: {
                exterior: { stroke: color },
                interior: { fill: color },
                pista: { text: 'Estado final' },
            },
        });
    }

    const pista = estado.actual ? 'estado actual' : estado.visitado ? 'visitado' : 'aún no visitado';

    return new Estado({
        position: posicion,
        attrs: {
            body: {
                fill: estado.visitado || estado.actual ? estado.color : colores.fondo,
                fillOpacity: estado.visitado || estado.actual ? 0.16 : 1,
                stroke: estado.visitado || estado.actual ? estado.color : colores.borde,
                strokeWidth: estado.actual ? 3 : 1.5,
                strokeDasharray: estado.visitado || estado.actual ? 'none' : '4 3',
            },
            estereotipo: { text: estado.actual ? '«actual»' : '', fill: colores.texto },
            nombre: {
                text: estado.nombre,
                y: estado.actual ? 36 : ESTADO.height / 2,
                fill: estado.visitado || estado.actual ? colores.texto : colores.muted,
            },
            pista: { text: `${estado.nombre} (${pista})` },
        },
    });
}

function crearTransicion(transicion, origen, destino, recorrida, disposicion, colores) {
    const { source, target, vertices, desfaseEtiqueta } = geometria(transicion, disposicion);
    const color = !recorrida ? colores.borde : transicion.rechazo ? colores.rechazo : colores.linea;
    const etiqueta = transicion.pasos.length > 0
        ? `${transicion.eventos.join(', ')} · #${transicion.pasos.join(' #')}`
        : '';

    return new shapes.standard.Link({
        source: { id: origen.id, ...source },
        target: { id: destino.id, ...target },
        vertices,
        connector: { name: 'rounded', args: { radius: 10 } },
        attrs: {
            line: {
                stroke: color,
                strokeWidth: recorrida ? 2 : 1.5,
                strokeDasharray: recorrida ? 'none' : '5 4',
                targetMarker: { type: 'path', d: 'M 10 -5 0 0 10 5', fill: 'none', stroke: color, strokeWidth: 1.5 },
            },
        },
        labels: etiqueta === '' ? [] : [{
            position: { distance: 0.5, offset: desfaseEtiqueta },
            attrs: {
                text: {
                    text: etiqueta,
                    fill: transicion.rechazo ? colores.rechazo : colores.texto,
                    fontSize: 11,
                    fontWeight: 600,
                    fontFamily: FUENTE,
                    ...alinear(desfaseEtiqueta),
                },
                rect: { fill: colores.fondo, fillOpacity: 0.85 },
            },
        }],
    });
}

export function initDiagramaEstados(contenedor, datos) {
    if (!contenedor || !datos || !Array.isArray(datos.estados) || !Array.isArray(datos.transiciones)) {
        return;
    }

    const estados = new Map(datos.estados.map((estado) => [estado.id, estado]));
    const ids = [INICIO, ...estados.keys(), FIN];
    const finalAlcanzado = datos.transiciones.some((t) => t.destino === FIN && estados.get(t.origen)?.actual);
    let papel = null;
    let ultimoAncho = 0;

    const dibujar = () => {
        const disponible = contenedor.clientWidth;

        if (!disponible) {
            return;
        }

        papel?.remove();
        contenedor.replaceChildren();

        const horizontal = calcularDisposicion(ids, 'horizontal');
        const disposicion = disponible >= horizontal.ancho * ESCALA_MINIMA_HORIZONTAL
            ? horizontal
            : calcularDisposicion(ids, 'vertical');
        const escala = Math.min(1, disponible / disposicion.ancho);
        const colores = coloresTema();
        const grafico = new dia.Graph({}, { cellNamespace: namespace });
        const nodos = new Map();

        ids.forEach((id) => {
            const { p, c } = disposicion.ubicaciones.get(id);
            const alcanzado = id === INICIO ? datos.pasos?.length > 0 : finalAlcanzado;
            const nodo = crearNodo(id, estados.get(id), vector(disposicion.o, p, c), alcanzado, colores);

            nodo.addTo(grafico);
            nodos.set(id, nodo);
        });

        datos.transiciones.forEach((transicion) => {
            const origen = nodos.get(transicion.origen);
            const destino = nodos.get(transicion.destino);

            if (!origen || !destino) {
                return;
            }

            const recorrida = transicion.pasos.length > 0 || (transicion.destino === FIN && finalAlcanzado);

            crearTransicion(transicion, origen, destino, recorrida, disposicion, colores).addTo(grafico);
        });

        papel = new dia.Paper({
            el: contenedor,
            model: grafico,
            width: Math.ceil(disposicion.ancho * escala),
            height: Math.ceil(disposicion.alto * escala),
            async: false,
            frozen: false,
            interactive: false,
            cellViewNamespace: namespace,
            defaultConnectionPoint: { name: 'anchor' },
            background: { color: 'transparent' },
        });
        papel.scale(escala);

        ultimoAncho = disponible;
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
