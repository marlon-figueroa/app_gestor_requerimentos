/**
 * Markup del loader Twin Orbit (estilo loading-ui): punto central y dos
 * marcadores en órbita con medio ciclo de desfase.
 */
export function twinOrbitMarkup({ label = 'Cargando…', size = 'lg' } = {}) {
    const sizeClass = size === 'sm' ? 'twin-orbit--sm' : size === 'md' ? 'twin-orbit--md' : 'twin-orbit--lg';

    return `
        <div class="twin-orbit ${sizeClass}" role="status" aria-live="polite">
            <span class="twin-orbit__center" aria-hidden="true"></span>
            <span class="twin-orbit__marker" aria-hidden="true"></span>
            <span class="twin-orbit__marker twin-orbit__marker--offset" aria-hidden="true"></span>
            <span class="visually-hidden">${label}</span>
        </div>
    `.trim();
}
