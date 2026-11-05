@extends('layouts.app')
@section('title', 'Data Produk')
@section('header', 'Data Produk')
@section('content')
<x-ui.page-header title="Data Produk" subtitle="Kelola katalog produk, harga, stok, dan status ketersediaan.">
    <x-slot:actions><x-ui.button :href="route('produk.create')"><x-ui.icon name="plus" /> Tambah Produk</x-ui.button></x-slot:actions>
</x-ui.page-header>
<x-ui.validation-summary />
<x-ui.data-table caption="Daftar Produk">
    <x-slot:toolbar>
        <x-ui.search-toolbar :action="route('produk.index')" :value="$cari" :filtered="(bool) ($status ?? '')" label="Cari Produk" placeholder="Cari kode, nama, atau kategori produk…">
            <x-ui.select name="status" label="Status Produk" id="filter-status">
                <option value="">Semua Status</option>
                <option value="aktif" @selected(($status ?? '') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(($status ?? '') === 'nonaktif')>Nonaktif</option>
            </x-ui.select>
        </x-ui.search-toolbar>
    </x-slot:toolbar>
    <thead><tr><th scope="col">No.</th><th scope="col">Produk</th><th scope="col">Kategori</th><th scope="col" class="numeric">Harga</th><th scope="col" class="numeric">Stok / Satuan</th><th scope="col">Status</th><th scope="col" class="numeric">Aksi</th></tr></thead>
    <tbody>
        @forelse($produk as $item)
            <tr>
                <td>{{ $produk->firstItem() + $loop->index }}</td>
                <td class="cell-copy"><p class="font-medium">{{ $item->nama_produk }}</p><p class="cell-meta">{{ $item->kode_produk }}</p></td>
<td>{{ $item->kategori_produk }}</td>
<td class="numeric"><x-ui.money :value="$item->harga_produk" /></td>
<td class="numeric">{{ number_format($item->stok_produk, 0, ',', '.') }} <span class="cell-meta">{{ $item->satuan_produk }}</span></td>
<td><x-ui.badge :status="$item->status_produk" /></td>
                <td><div class="table-actions">
                    <x-ui.button :href="route('produk.edit', $item)" variant="secondary" :aria-label="'Ubah Produk '.$item->nama_produk">Ubah</x-ui.button>
                    @if($item->status_produk === 'aktif')
                        <x-ui.record-action :action="route('produk.nonaktifkan', $item)" :message="'Nonaktifkan Produk '.$item->nama_produk.'?'" label="Nonaktifkan" />

                    @endif
                </div></td>
            </tr>
        @empty
            <x-ui.list-empty colspan="7" :filtered="(bool) $cari || (bool) ($status ?? '')" title="Belum Ada Data Produk" description="Tambahkan data Produk untuk mulai digunakan dalam pencatatan penjualan." :create="route('produk.create')" create-label="Tambah Produk" :reset="route('produk.index')" />
        @endforelse
    </tbody>
    <x-slot:pagination><x-ui.pagination :paginator="$produk" /></x-slot:pagination>
</x-ui.data-table>
@endsection
