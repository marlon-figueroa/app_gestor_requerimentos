{{-- Debe ir en <head> antes de los estilos para evitar el destello del tema incorrecto. --}}
<script>
    (() => {
        let theme = 'auto';
        try { theme = localStorage.getItem('theme') || 'auto'; } catch (e) {}
        if (theme !== 'light' && theme !== 'dark') {
            theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-bs-theme', theme);
    })();
</script>
