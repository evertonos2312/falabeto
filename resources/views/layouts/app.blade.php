<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $productName = settings('branding.product_name', 'Fala, Beto!');
        $faviconPath = settings('branding.favicon_path');
        $faviconUrl = $faviconPath ? asset('storage/' . $faviconPath) : asset('favicon.ico');
    @endphp
    <title>{{ $title ?? $productName }}</title>
    <link rel="icon" href="{{ $faviconUrl }}">
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="bg-slate-950 text-slate-100">
    <div class="min-h-screen">
        {{ $slot }}
    </div>
    @livewireScripts
    <script>
        (function () {
            const openModal = (name) => {
                const modal = document.querySelector(`[data-modal="${name}"]`);
                if (!modal) return;
                modal.style.display = 'flex';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                requestAnimationFrame(() => {
                    modal.querySelector('[data-modal-overlay]')?.classList.add('opacity-100');
                    modal.querySelector('[data-modal-panel]')?.classList.add('opacity-100', 'translate-y-0');
                });
            };

            const closeModal = (modal) => {
                const overlay = modal.querySelector('[data-modal-overlay]');
                const panel = modal.querySelector('[data-modal-panel]');
                overlay?.classList.remove('opacity-100');
                panel?.classList.remove('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.style.display = 'none';
                }, 200);
            };

            document.addEventListener('click', (event) => {
                const open = event.target.closest('[data-open-modal]');
                if (open) {
                    event.preventDefault();
                    openModal(open.getAttribute('data-open-modal'));
                    return;
                }

                const close = event.target.closest('[data-close-modal]');
                if (close) {
                    const modal = close.closest('[data-modal]');
                    if (modal) closeModal(modal);
                    return;
                }

                const overlay = event.target.closest('[data-modal-overlay]');
                if (overlay) {
                    const modal = overlay.closest('[data-modal]');
                    if (modal) closeModal(modal);
                }
            });
        })();
    </script>
</body>
</html>
