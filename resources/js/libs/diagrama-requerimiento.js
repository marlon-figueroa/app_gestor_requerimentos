import { dia, shapes } from '@joint/core';

const FUENTE = 'Nunito, sans-serif';
const ESTADO = { width: 128, height: 56 };
const ANCHO_DETALLE = 248;
const CABECERA = 36;
const CABECERA_ACTUAL = 48;
const ALTO_LINEA = 13;
const SEPARACION_EVENTO = 8;
const RELLENO_INFERIOR = 10;
const MAX_CARACTERES = 44;
const MAX_LINEAS_OBSERVACION = 3;
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
            x: 'calc(w/2)',
            y: 14,
            textAnchor: 'middle',
            textVerticalAnchor: 'middle',
            fontSize: 10,
            fontStyle: 'italic',
            fontFamily: FUENTE,
        },
        nombre: {
            x: 'calc(w/2)',
            textAnchor: 'middle',
            textVerticalAnchor: 'middle',
            fontSize: 13,
            fontWeight: 700,
            fontFamily: FUENTE,
        },
        separador: {
            x1: 12,
            x2: 'calc(w-12)',
            y1: CABECERA,
            y2: CABECERA,
            strokeWidth: 1,
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

function tamano(id, medidas) {
    if (id === INICIO) {
        return INICIAL;
    }

    if (id === FIN) {
        return FINAL;
    }

    return medidas.get(id) ?? ESTADO;
}

function partirLinea(texto, maximo) {
    const limpio = String(texto).replace(/\s+/g, ' ').trim();

    if (limpio === '') {
        return [];
    }

    const lineas = [];
    let resto = limpio;

    while (resto.length > maximo) {
        const corte = resto.lastIndexOf(' ', maximo);
        const punto = corte > 0 ? corte : maximo;
        lineas.push(resto.slice(0, punto).trim());
        resto = resto.slice(punto).trim();
    }

    if (resto !== '') {
        lineas.push(resto);
    }

    return lineas;
}

function limitarLineas(lineas, maximo) {
    if (lineas.length <= maximo) {
        return lineas;
    }

    const visibles = lineas.slice(0, maximo);
    const ultima = visibles[maximo - 1];
    visibles[maximo - 1] = `${ultima.replace(/…$/, '').slice(0, Math.max(0, MAX_CARACTERES - 1))}…`;

    return visibles;
}

function lineasEvento(paso) {
    const lineas = partirLinea(`#${paso.numero} ${paso.accion}`, MAX_CARACTERES);

    if (paso.actor) {
        lineas.push(...partirLinea(paso.actor, MAX_CARACTERES));
    }

    if (paso.fecha) {
        lineas.push(paso.fecha);
    }

    if (paso.observaciones) {
        lineas.push(...limitarLineas(partirLinea(paso.observaciones, MAX_CARACTERES), MAX_LINEAS_OBSERVACION));
    }

    return lineas;
}

function medirEstado(eventos = [], actual = false) {
    if (!eventos.length) {
        return { width: ESTADO.width, height: ESTADO.height, bloques: [], cabecera: CABECERA };
    }

    const cabecera = actual ? CABECERA_ACTUAL : CABECERA;
    let cursor = cabecera + 8;
    const bloques = eventos.map((paso) => {
        const lineas = lineasEvento(paso);
        const bloque = {
            y: cursor,
            texto: lineas.join('\n'),
            rechazo: Boolean(paso.rechazo),
            pista: [`#${paso.numero} ${paso.accion}`, paso.actor, paso.fecha, paso.observaciones].filter(Boolean).join(' — '),
        };

        cursor += lineas.length * ALTO_LINEA + SEPARACION_EVENTO;

        return bloque;
    });

    return {
        width: ANCHO_DETALLE,
        height: cursor - SEPARACION_EVENTO + RELLENO_INFERIOR,
        bloques,
        cabecera,
    };
}

function medidasEstados(estados) {
    return new Map(estados.map((estado) => [estado.id, medirEstado(estado.eventos ?? [], Boolean(estado.actual))]));
}

function markupEstado(cantidad) {
    const markup = [
        { tagName: 'rect', selector: 'body' },
        { tagName: 'text', selector: 'estereotipo' },
        { tagName: 'text', selector: 'nombre' },
    ];

    if (cantidad > 0) {
        markup.push({ tagName: 'line', selector: 'separador' });

        for (let indice = 0; indice < cantidad; indice += 1) {
            markup.push({ tagName: 'text', selector: `evento-${indice}` });
        }
    }

    markup.push({ tagName: 'title', selector: 'pista' });

    return markup;
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

function calcularDisposicion(ids, orientacion, medidas, ubicacion = UBICACION) {
    const o = ORIENTACIONES[orientacion];
    const ejes = (medida) => (o.principal === 'x' ? [medida.width, medida.height] : [medida.height, medida.width]);
    const nodos = ids.map((id, indice) => {
        const [principal, cruzado] = ubicacion[id] ?? [indice, 2];
        const [largo, ancho] = ejes(tamano(id, medidas));

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

function centro(nodo) {
    return { p: nodo.p + nodo.largo / 2, c: nodo.c + nodo.ancho / 2 };
}

function codo(origen, destino, baja, o) {
    const avanza = destino.principal > origen.principal;
    const desde = centro(origen);
    const hasta = centro(destino);

    if (baja) {
        const tramoCruzado = Math.abs(hasta.c - (origen.c + origen.ancho));
        const tramoPrincipal = Math.abs((avanza ? destino.p : destino.p + destino.largo) - desde.p);

        return {
            source: { anchor: { name: o.finCruzado } },
            target: { anchor: { name: avanza ? o.inicioPrincipal : o.finPrincipal } },
            vertices: [vector(o, desde.p, hasta.c)],
            distanciaEtiqueta: tramoCruzado + tramoPrincipal / 2,
            desfaseEtiqueta: vector(o, 0, -6),
        };
    }

    const tramoPrincipal = Math.abs(hasta.p - (avanza ? origen.p + origen.largo : origen.p));

    return {
        source: { anchor: { name: avanza ? o.finPrincipal : o.inicioPrincipal } },
        target: { anchor: { name: o.finCruzado } },
        vertices: [vector(o, hasta.p, desde.c)],
        distanciaEtiqueta: tramoPrincipal / 2,
        desfaseEtiqueta: vector(o, 0, -6),
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

    if (origen.principal !== destino.principal) {
        return codo(origen, destino, baja, o);
    }

    return {
        source: { anchor: { name: baja ? o.finCruzado : o.inicioCruzado, args: desplazar(o, signo * SEPARACION_PARALELA) } },
        target: { anchor: { name: baja ? o.inicioCruzado : o.finCruzado, args: desplazar(o, signo * SEPARACION_PARALELA) } },
        vertices: [],
        desfaseEtiqueta: vector(o, signo * 8, 0),
    };
}

function crearNodo(id, estado, posicion, alcanzado, colores, medida) {
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
    const bloques = medida?.bloques ?? [];
    const detalle = bloques.map((bloque) => bloque.pista).join('\n');
    const attrs = {
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
            y: bloques.length ? (estado.actual ? 32 : 20) : (estado.actual ? 36 : 'calc(h/2)'),
            fill: estado.visitado || estado.actual ? colores.texto : colores.muted,
        },
        pista: { text: detalle === '' ? `${estado.nombre} (${pista})` : `${estado.nombre} (${pista})\n${detalle}` },
    };

    if (bloques.length) {
        attrs.separador = { stroke: colores.borde, y1: medida.cabecera, y2: medida.cabecera };

        bloques.forEach((bloque, indice) => {
            attrs[`evento-${indice}`] = {
                x: 12,
                y: bloque.y,
                text: bloque.texto,
                fill: bloque.rechazo ? colores.rechazo : colores.texto,
                textAnchor: 'start',
                textVerticalAnchor: 'top',
                fontSize: 10,
                lineHeight: `${ALTO_LINEA}px`,
                fontFamily: FUENTE,
            };
        });
    }

    return new Estado({
        position: posicion,
        size: { width: medida.width, height: medida.height },
        markup: markupEstado(bloques.length),
        attrs,
    });
}

function crearTransicion(transicion, origen, destino, recorrida, disposicion, colores) {
    const { source, target, vertices, desfaseEtiqueta, distanciaEtiqueta = 0.5 } = geometria(transicion, disposicion);
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
            position: { distance: distanciaEtiqueta, offset: desfaseEtiqueta },
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
    const medidas = medidasEstados(datos.estados);
    const ubicacion = datos.ubicacion ?? UBICACION;
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

        const horizontal = calcularDisposicion(ids, 'horizontal', medidas, ubicacion);
        const disposicion = disponible >= horizontal.ancho * ESCALA_MINIMA_HORIZONTAL
            ? horizontal
            : calcularDisposicion(ids, 'vertical', medidas, ubicacion);
        const proporcion = disponible / disposicion.ancho;
        const escala = proporcion >= 0.8 ? Math.min(1, proporcion) : 1;
        const colores = coloresTema();
        const grafico = new dia.Graph({}, { cellNamespace: namespace });
        const nodos = new Map();

        ids.forEach((id) => {
            const { p, c } = disposicion.ubicaciones.get(id);
            const alcanzado = id === INICIO ? datos.pasos?.length > 0 : finalAlcanzado;
            const nodo = crearNodo(id, estados.get(id), vector(disposicion.o, p, c), alcanzado, colores, medidas.get(id));

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
            model: grafico,
            width: Math.floor(disposicion.ancho * escala),
            height: Math.ceil(disposicion.alto * escala),
            async: false,
            frozen: true,
            interactive: false,
            cellViewNamespace: namespace,
            defaultConnectionPoint: { name: 'anchor' },
            background: { color: 'transparent' },
        });
        papel.scale(escala);
        contenedor.replaceChildren(papel.el);
        papel.unfreeze();

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
    contenedor.closest('.modal')?.addEventListener('shown.bs.modal', () => {
        ultimoAncho = 0;
        dibujar();
    });
    document.addEventListener('theme:changed', () => {
        ultimoAncho = 0;
        dibujar();
    });
}
