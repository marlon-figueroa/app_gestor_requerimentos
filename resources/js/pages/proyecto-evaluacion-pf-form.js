const definicion = JSON.parse(document.getElementById('pf-definicion-datos')?.textContent ?? '{}');
const cuerpo = document.getElementById('pf-cuerpo-componentes');
const btnAgregar = document.getElementById('pf-agregar-fila');

const TIPOS_ARCHIVO = new Set(['ilf', 'eif']);
const INDICE_COMPLEJIDAD = { baja: 0, media: 1, alta: 2 };

function indiceDetTransaccion(det) {
    if (det <= 4) return 0;
    if (det <= 15) return 1;
    return 2;
}

function indiceFtr(ftr) {
    if (ftr <= 1) return 0;
    if (ftr === 2) return 1;
    return 2;
}

function indiceDetArchivo(det) {
    if (det <= 19) return 0;
    if (det <= 50) return 1;
    return 2;
}

function indiceRet(ret) {
    if (ret <= 1) return 0;
    if (ret <= 5) return 1;
    return 2;
}

function evaluarFila(tipo, det, referencias) {
    const detN = Number(det) || 0;
    const refN = Number(referencias) || 0;
    let complejidad;

    if (TIPOS_ARCHIVO.has(tipo)) {
        complejidad = definicion.matrizArchivo[indiceDetArchivo(detN)][indiceRet(refN)];
    } else {
        complejidad = definicion.matrizTransaccion[indiceDetTransaccion(detN)][indiceFtr(refN)];
    }

    const indice = INDICE_COMPLEJIDAD[complejidad] ?? 0;

    return definicion.pesos[tipo]?.[indice] ?? 0;
}

function actualizarEtiquetaReferencia(fila) {
    const tipo = fila.querySelector('.pf-tipo')?.value ?? 'ei';
    const th = document.querySelector('#pf-tabla-componentes .pf-col-ref');
    if (th) {
        th.textContent = TIPOS_ARCHIVO.has(tipo) ? 'RET' : 'FTR';
    }
}

function recalcular() {
    let total = 0;

    cuerpo?.querySelectorAll('.pf-fila-componente').forEach((fila) => {
        const tipo = fila.querySelector('.pf-tipo')?.value ?? 'ei';
        const det = fila.querySelector('.pf-det')?.value ?? 0;
        const ref = fila.querySelector('.pf-referencias')?.value ?? 0;
        const puntos = evaluarFila(tipo, det, ref);
        const celda = fila.querySelector('.pf-puntos-fila');

        if (celda) {
            celda.textContent = String(puntos);
        }

        total += puntos;
        actualizarEtiquetaReferencia(fila);
    });

    const caracteristicas = [...document.querySelectorAll('.pf-caracteristica')].map((el) => Number(el.value) || 0);
    const sumaInfluencia = caracteristicas.reduce((acc, v) => acc + v, 0);
    const vaf = Math.round((0.65 + sumaInfluencia * 0.01) * 100) / 100;
    const ajustado = Math.round(total * vaf * 100) / 100;

    const fmt = (n) => n.toLocaleString('es-SV', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    document.getElementById('pf-resumen-sin-ajuste').textContent = String(total);
    document.getElementById('pf-resumen-influencia').textContent = String(sumaInfluencia);
    document.getElementById('pf-resumen-vaf').textContent = fmt(vaf);
    document.getElementById('pf-resumen-ajustado').textContent = fmt(ajustado);
}

function plantillaFila(indice) {
    const opciones = ['ei', 'eo', 'eq', 'ilf', 'eif']
        .map((t) => `<option value="${t}">${t.toUpperCase()}</option>`)
        .join('');

    return `
        <tr class="pf-fila-componente" data-indice="${indice}">
            <td><select name="componentes[${indice}][tipo]" class="form-select form-select-sm pf-tipo" required>${opciones}</select></td>
            <td>
                <input type="text" name="componentes[${indice}][nombre]" class="form-control form-control-sm" required maxlength="200" placeholder="Descripción breve">
                <input type="hidden" name="componentes[${indice}][descripcion]" value="">
            </td>
            <td><input type="number" name="componentes[${indice}][referencias]" class="form-control form-control-sm pf-referencias" value="1" min="0" max="999" required></td>
            <td><input type="number" name="componentes[${indice}][det]" class="form-control form-control-sm pf-det" value="1" min="0" max="999" required></td>
            <td class="text-end pf-puntos-fila text-body-secondary">—</td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger pf-quitar-fila" title="Quitar fila"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
}

function reindexarFilas() {
    cuerpo?.querySelectorAll('.pf-fila-componente').forEach((fila, indice) => {
        fila.dataset.indice = String(indice);
        fila.querySelectorAll('[name^="componentes["]').forEach((input) => {
            input.name = input.name.replace(/componentes\[\d+]/, `componentes[${indice}]`);
        });
    });
}

function siguienteIndice() {
    return cuerpo?.querySelectorAll('.pf-fila-componente').length ?? 0;
}

btnAgregar?.addEventListener('click', () => {
    cuerpo?.insertAdjacentHTML('beforeend', plantillaFila(siguienteIndice()));
    recalcular();
});

cuerpo?.addEventListener('click', (event) => {
    const btn = event.target.closest('.pf-quitar-fila');
    if (!btn) return;

    const filas = cuerpo.querySelectorAll('.pf-fila-componente');
    if (filas.length <= 1) return;

    btn.closest('tr')?.remove();
    reindexarFilas();
    recalcular();
});

cuerpo?.addEventListener('input', (event) => {
    if (event.target.closest('.pf-fila-componente')) {
        recalcular();
    }
});

document.querySelectorAll('.pf-caracteristica').forEach((el) => {
    el.addEventListener('change', recalcular);
});

recalcular();
