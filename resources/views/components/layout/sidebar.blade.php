@props(['user'])
<aside id="app-sidebar" class="app-sidebar" aria-label="Navigasi aplikasi">
    <div class="sidebar-brand">
        <div><p class="brand-name">PT. Halus Ciptanadi</p><p class="brand-tagline">Pencatatan Penjualan</p></div>
        <button type="button" class="icon-button sidebar-close" data-navigation-close aria-label="Tutup menu navigasi"><x-ui.icon name="x" /></button>
    </div>
    <nav class="sidebar-nav" aria-label="Menu utama">
        <x-layout.sidebar-item :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="grid">Halaman Utama</x-layout.sidebar-item>
        @if($user->role === 'admin')
            <x-layout.sidebar-item :href="route('sales.index')" :active="request()->routeIs('sales.*')" icon="users">Data Sales</x-layout.sidebar-item>
            <x-layout.sidebar-item :href="route('produk.index')" :active="request()->routeIs('produk.*')" icon="box">Data Produk</x-layout.sidebar-item>
            <x-layout.sidebar-item :href="route('transaksi.index')" :active="request()->routeIs('transaksi.*')" icon="receipt">Transaksi Penjualan</x-layout.sidebar-item>
        @endif
        @if(in_array($user->role, ['admin', 'sales'], true))
            <x-layout.sidebar-item :href="route('pelanggan.index')" :active="request()->routeIs('pelanggan.*')" icon="user">Data Pelanggan</x-layout.sidebar-item>
        @endif
        @if($user->role === 'sales')
            <x-layout.sidebar-item :href="route('order.index')" :active="request()->routeIs('order.*')" icon="receipt">Order Penjualan</x-layout.sidebar-item>
        @endif
    </nav>
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" data-submit-state>
            @csrf
            <button type="submit" class="sidebar-item sidebar-logout" data-loading-text="Keluar…"><x-ui.icon name="logout" /><span data-button-label>Keluar</span></button>
        </form>
    </div>
</aside>
