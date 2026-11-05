@extends('layouts.app')
@section('title', 'Data Pelanggan')
@section('header', 'Data Pelanggan')
@section('content')
<x-ui.page-header title="Data Pelanggan" subtitle="Kelola identitas pelanggan, informasi toko, dan kontak WhatsApp.">
    <x-slot:actions><x-ui.button :href="route('pelanggan.create')"><x-ui.icon name="plus" /> Tambah Pelanggan</x-ui.button></x-slot:actions>
</x-ui.page-header>
<x-ui.validation-summary />
<x-ui.data-table caption="Daftar Pelanggan">
    <x-slot:toolbar>
        <x-ui.search-toolbar :action="route('pelanggan.index')" :value="$cari" :filtered="(bool) ($status ?? '')" label="Cari Pelanggan" placeholder="Cari pelanggan, toko, kode, atau WhatsApp…">
            <x-ui.select name="status" label="Status Pelanggan" id="filter-status">
                <option value="">Semua Status</option>
                <option value="aktif" @selected(($status ?? '') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(($status ?? '') === 'nonaktif')>Nonaktif</option>
            </x-ui.select>
        </x-ui.search-toolbar>
    </x-slot:toolbar>
    <thead><tr><th scope="col">No.</th><th scope="col">Pelanggan</th><th scope="col">Nama Toko</th><th scope="col">WhatsApp</th><th scope="col">Alamat</th><th scope="col">Status</th><th scope="col" class="numeric">Aksi</th></tr></thead>
    <tbody>
        @forelse($pelanggan as $item)
            <tr>
                <td>{{ $pelanggan->firstItem() + $loop->index }}</td>
                <td class="cell-copy"><p class="font-medium">{{ $item->nama_pelanggan }}</p><p class="cell-meta">{{ $item->kode_pelanggan }}</p></td>
<td class="cell-copy">{{ $item->nama_toko }}</td>
<td class="whitespace-nowrap">{{ $item->no_wa }}</td>
<td class="cell-address">{{ $item->alamat_pelanggan }}</td>
<td><x-ui.badge :status="$item->status_pelanggan" /></td>
                <td><div class="table-actions">
                    @if(auth()->user()?->role === 'admin')
                        <x-ui.button :href="route('pelanggan.edit', $item)" variant="secondary" :aria-label="'Ubah Pelanggan '.$item->nama_pelanggan">Ubah</x-ui.button>
                        @if($item->status_pelanggan === 'aktif')
                            <x-ui.record-action :action="route('pelanggan.nonaktifkan', $item)" :message="'Nonaktifkan Pelanggan '.$item->nama_pelanggan.'?'" label="Nonaktifkan" />
                        @endif
                    @else
                        <span class="cell-meta">—</span>
                    @endif
                </div></td>
            </tr>
        @empty
            <x-ui.list-empty colspan="7" :filtered="(bool) $cari || (bool) ($status ?? '')" title="Belum Ada Data Pelanggan" description="Tambahkan data Pelanggan untuk mulai digunakan dalam pencatatan penjualan." :create="route('pelanggan.create')" create-label="Tambah Pelanggan" :reset="route('pelanggan.index')" />
        @endforelse
    </tbody>
    <x-slot:pagination><x-ui.pagination :paginator="$pelanggan" /></x-slot:pagination>
</x-ui.data-table>
@endsection
