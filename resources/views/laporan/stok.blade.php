@extends('layouts.app')
@section('title', 'Laporan Stok')
@section('header', 'Laporan Stok')
@section('content')
<x-ui.page-header title="Laporan Stok" subtitle="Informasi ketersediaan dan jumlah stok seluruh produk.">
    <x-slot:actions>
        <x-ui.button data-print class="no-print">
            <x-ui.icon name="box" />
            <span>Cetak Laporan</span>
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<div class="print-brand print-report-header">
    <div class="print-brand-row">
        <div>
            <p class="brand-name">PT. Halus Ciptanadi</p>
            <p class="print-report-subtitle">Sistem Informasi Penjualan</p>
        </div>
        <div class="text-right">
            <h2 class="print-report-title">Laporan Stok</h2>
            <p class="print-report-timestamp">Dicetak pada: {{ now()->locale('id')->translatedFormat('d F Y H:i') }}</p>
        </div>
    </div>
    <div class="print-filter-summary">
        <div><strong>Status Produk:</strong> Semua Produk (Aktif &amp; Nonaktif)</div>
    </div>
</div>

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
        <div class="no-print">
            <x-ui.pagination :paginator="$produk" />
        </div>
    </x-slot:pagination>
</x-ui.data-table>

<div class="print-report-footer print-only">
    <p>Dicetak melalui Sistem Informasi Penjualan PT. Halus Ciptanadi · Dokumen Internal Direktur</p>
</div>
@endsection
