@extends('layouts.app')
@section('title', 'Dashboard Direktur')
@section('header', 'Halaman Utama')
@section('content')
<x-ui.page-header title="Dashboard Direktur" subtitle="Pantau penjualan yang telah selesai dan kondisi stok produk aktif." />
<div class="ui-stat-grid">
    <x-ui.stat-card label="Total Pendapatan" :value="'Rp '.number_format($totalPenjualan, (float) $totalPenjualan == round((float) $totalPenjualan) ? 0 : 2, ',', '.')" tone="success" icon="money" description="Transaksi selesai · seluruh periode" />
    <x-ui.stat-card label="Transaksi Selesai" :value="number_format($totalTransaksi, 0, ',', '.')" icon="receipt" description="Seluruh periode" />
    <x-ui.stat-card label="Produk Aktif" :value="number_format($totalProduk, 0, ',', '.')" tone="info" icon="box" description="Produk dalam katalog aktif" />
    <x-ui.stat-card label="Stok Rendah" :value="number_format($stokRendah, 0, ',', '.')" tone="warning" icon="warning" description="Produk aktif dengan stok ≤ 10" />
</div>
<div class="dashboard-section"><x-ui.card title="Tentang Ringkasan Ini"><p class="page-subtitle mt-0">Pendapatan hanya menghitung transaksi berstatus Selesai. Jumlah stok rendah mengikuti satuan masing-masing produk.</p></x-ui.card></div>
@endsection
