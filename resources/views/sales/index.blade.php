@extends('layouts.app')
@section('title', 'Data Sales')
@section('header', 'Data Sales')
@section('content')
<x-ui.page-header title="Data Sales" subtitle="Kelola akun, informasi kontak, dan status tenaga penjualan.">
    <x-slot:actions><x-ui.button :href="route('sales.create')"><x-ui.icon name="plus" /> Tambah Sales</x-ui.button></x-slot:actions>
</x-ui.page-header>
<x-ui.validation-summary />
<x-ui.data-table caption="Daftar Sales">
    <x-slot:toolbar>
        <x-ui.search-toolbar :action="route('sales.index')" :value="$cari" :filtered="(bool) ($status ?? '')" label="Cari Sales" placeholder="Cari nama, username, atau nomor telepon…">
            <x-ui.select name="status" label="Status Akun" id="filter-status">
                <option value="">Semua Status</option>
                <option value="aktif" @selected(($status ?? '') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(($status ?? '') === 'nonaktif')>Nonaktif</option>
            </x-ui.select>
        </x-ui.search-toolbar>
    </x-slot:toolbar>
    <thead><tr><th scope="col">No.</th><th scope="col">Nama Sales</th><th scope="col">Username</th><th scope="col">Kontak</th><th scope="col">Alamat</th><th scope="col">Status</th><th scope="col" class="numeric">Aksi</th></tr></thead>
    <tbody>
        @forelse($sales as $item)
            <tr>
                <td>{{ $sales->firstItem() + $loop->index }}</td>
                <td class="cell-copy font-medium">{{ $item->nama_user }}</td>
<td>{{ $item->username }}</td>
<td class="whitespace-nowrap">{{ $item->no_telp ?: '—' }}</td>
<td class="cell-address">{{ $item->alamat ?: '—' }}</td>
<td><x-ui.badge :status="$item->status_user" /></td>
                <td><div class="table-actions">
                    <x-ui.button :href="route('sales.edit', $item)" variant="secondary" :aria-label="'Ubah Sales '.$item->nama_user">Ubah</x-ui.button>
                    @if($item->status_user === 'aktif')
                        <x-ui.record-action :action="route('sales.nonaktifkan', $item)" :message="'Nonaktifkan Sales '.$item->nama_user.'?'" label="Nonaktifkan" />
                    @else
<x-ui.record-action :action="route('sales.aktifkan', $item)" :message="'Aktifkan kembali akun '.$item->nama_user.'?'" label="Aktifkan" variant="secondary" />
                    @endif
                </div></td>
            </tr>
        @empty
            <x-ui.list-empty colspan="7" :filtered="(bool) $cari || (bool) ($status ?? '')" title="Belum Ada Data Sales" description="Tambahkan data Sales untuk mulai digunakan dalam pencatatan penjualan." :create="route('sales.create')" create-label="Tambah Sales" :reset="route('sales.index')" />
        @endforelse
    </tbody>
    <x-slot:pagination><x-ui.pagination :paginator="$sales" /></x-slot:pagination>
</x-ui.data-table>
@endsection
