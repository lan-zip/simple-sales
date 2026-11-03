@extends('layouts.app')
@section('title', 'Detail Transaksi')
@section('header', 'Transaksi Penjualan')
@section('content')
<x-ui.page-header title="Detail Transaksi" :subtitle="'Periksa rincian transaksi '.$transaksi->nomor_penjualan.'.'" class="no-print">
    <x-slot:badge><x-ui.badge :status="$transaksi->status_penjualan" /></x-slot:badge>
    <x-slot:actions>
        @if($transaksi->isMenungguValidasi())
            <x-ui.record-action :action="route('transaksi.validasi', $transaksi)" message="Validasi transaksi ini? Stok produk akan dikurangi dan status transaksi berubah menjadi diproses." label="Validasi Transaksi" variant="primary" />
        @elseif($transaksi->isDiproses())
            <x-ui.record-action :action="route('transaksi.kirim', $transaksi)" message="Ubah status transaksi menjadi dikirim? Pastikan pesanan siap atau sedang dalam proses pengiriman." label="Kirim Pesanan" variant="primary" />
        @endif
        @if($transaksi->canPrintNota())
            <x-ui.button data-print>Cetak Nota</x-ui.button>
        @endif
    </x-slot:actions>
</x-ui.page-header>
@if($transaksi->isMenungguValidasi())
    <x-ui.alert type="info" class="mb-6">Pastikan pelanggan, produk, dan jumlah sudah sesuai sebelum melakukan validasi.</x-ui.alert>
@elseif($transaksi->isDiproses())
    <x-ui.alert type="info" class="mb-6">Transaksi telah divalidasi dan sedang diproses. Klik "Kirim Pesanan" saat barang diserahkan untuk pengiriman.</x-ui.alert>
@elseif($transaksi->isDikirim())
    <x-ui.alert type="info" class="mb-6">Pesanan sedang dalam tahap pengiriman. Menunggu Sales mengunggah bukti penyerahan dan menyelesaikan pesanan.</x-ui.alert>
@endif
@include('shared.sale-detail', ['sale' => $transaksi, 'isAdmin' => true])
@endsection
