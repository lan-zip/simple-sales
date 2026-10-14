<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6F0D0D">
    <title>@yield('title') | PT. Halus Ciptanadi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>
    <div class="app-shell" data-app-shell>
        <x-layout.sidebar :user="auth()->user()" />
        <div class="app-workspace" data-app-workspace>
            <x-layout.topbar :user="auth()->user()">
                @hasSection('topbar-search')
                    @yield('topbar-search')
                @else
                    <p class="topbar-context">@yield('header', 'Pencatatan Penjualan')</p>
                @endif
            </x-layout.topbar>
            <main id="main-content" class="app-content" tabindex="-1">
                <x-ui.flash-messages />
                @yield('content')
            </main>
        </div>
    </div>
    <dialog id="navigation-dialog" class="navigation-dialog" aria-label="Menu navigasi"></dialog>
    <x-ui.modal id="confirmation-dialog" title="Konfirmasi Tindakan">
        <p data-confirmation-message></p>
        <x-slot:actions>
            <x-ui.button variant="secondary" data-dialog-close>Batal</x-ui.button>
            <x-ui.button variant="danger" data-confirmation-accept>Lanjutkan</x-ui.button>
        </x-slot:actions>
    </x-ui.modal>
    @stack('scripts')
</body>
</html>
