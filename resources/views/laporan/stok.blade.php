@extends('layouts.app')
@section('title', 'Laporan Stok')
@section('header', 'Laporan Stok')
@section('content')
<x-ui.page-header title="Laporan Stok" subtitle="Informasi ketersediaan dan jumlah stok seluruh produk." />

<x-ui.data-table caption="Daftar laporan stok produk">
    <thead>
        <tr>
            <th scope="col">No.</th>
            <th scope="col">Kode Produk</th>
            <th scope="col">Nama Produk</th>
            <th scope="col">Kategori</th>
            <th scope="col" class="numeric">Stok</th>
            <th scope="col">Satuan</th>
            <th scope="col">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($produk as $item)
            <tr>
                <td>{{ $produk->firstItem() + $loop->index }}</td>
                <td><span class="cell-meta font-mono">{{ $item->kode_produk }}</span></td>
                <td class="cell-copy font-medium">{{ $item->nama_produk }}</td>
                <td>{{ $item->kategori_produk }}</td>
                <td class="numeric font-medium">{{ number_format($item->stok_produk, 0, ',', '.') }}</td>
                <td>{{ $item->satuan_produk }}</td>
                <td><x-ui.badge :status="$item->status_produk" /></td>
            </tr>
        @empty
            <x-ui.list-empty :colspan="7" title="Belum Ada Data Produk" description="Data produk yang tersimpan di sistem akan ditampilkan di sini." />
        @endforelse
    </tbody>
    <x-slot:pagination>
        <x-ui.pagination :paginator="$produk" />
    </x-slot:pagination>
</x-ui.data-table>
@endsection
