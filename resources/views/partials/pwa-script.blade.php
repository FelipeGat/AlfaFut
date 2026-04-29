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
        const banner = document.getElementById('banner-instalar');
        const btn = document.getElementById('btn-instalar');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;

            // Mostrar banner via Alpine
            if (banner && banner.__x) {
                banner.__x.$data.aberto = true;
            } else if (banner) {
                // fallback - dispara depois do Alpine carregar
                setTimeout(() => {
                    if (banner.__x) banner.__x.$data.aberto = true;
                }, 500);
            }
        });

        if (btn) {
            btn.addEventListener('click', async () => {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                console.log('Instalacao:', outcome);
            });
        }

        window.addEventListener('appinstalled', () => {
            console.log('AlfaFut instalado.');
            deferredPrompt = null;
        });
    })();
</script>
