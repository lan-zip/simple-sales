@extends('layouts.app')
@section('title', 'Detail Transaksi')
@section('header', 'Transaksi Penjualan')
@section('content')
<x-ui.page-header title="Detail Transaksi" :subtitle="'Periksa rincian transaksi '.$transaksi->nomor_penjualan.'.'">
    <x-slot:badge><x-ui.badge :status="$transaksi->status_penjualan" /></x-slot:badge>
    @if($transaksi->status_penjualan === 'menunggu_validasi')
        <x-slot:actions><x-ui.record-action :action="route('transaksi.validasi', $transaksi)" message="Validasi transaksi ini? Stok produk akan dikurangi dan sistem akan mencoba mengirim nota WhatsApp kepada pelanggan." label="Validasi Transaksi" variant="primary" /></x-slot:actions>
    @endif
</x-ui.page-header>
@if($transaksi->status_penjualan === 'menunggu_validasi')
    <x-ui.alert type="info" class="mb-6">Pastikan pelanggan, produk, dan jumlah sudah sesuai sebelum melakukan validasi.</x-ui.alert>
@endif
@include('shared.sale-detail', ['sale' => $transaksi, 'isAdmin' => true])
@endsection
