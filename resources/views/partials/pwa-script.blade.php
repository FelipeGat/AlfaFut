{{-- PWA: registra Service Worker + intercepta beforeinstallprompt --}}
<script>
    (function () {
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch((err) => {
                    console.warn('SW falhou ao registrar:', err);
                });
            });
        }

        let deferredPrompt = null;
        const btn = document.getElementById('btn-instalar');
        const abrirBanner = () => window.dispatchEvent(new CustomEvent('alfafut-instalar'));

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            abrirBanner();
        });

        // O navegador pode oferecer a instalação antes de o Alpine iniciar.
        document.addEventListener('alpine:initialized', () => {
            if (deferredPrompt) abrirBanner();
        });

        if (btn) {
            btn.addEventListener('click', async () => {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;
            });
        }

        window.addEventListener('appinstalled', () => {
            deferredPrompt = null;
        });
    })();
</script>
