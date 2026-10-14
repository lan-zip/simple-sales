@extends('layouts.app')
@section('title', 'Dashboard Sales')
@section('header', 'Halaman Utama')
@section('content')
<x-ui.page-header title="Dashboard Sales" subtitle="Ringkasan seluruh order yang Anda catat dan status pemrosesannya.">
    <x-slot:actions><x-ui.button :href="route('order.create')"><x-ui.icon name="plus" /> Tambah Order</x-ui.button></x-slot:actions>
</x-ui.page-header>
<div class="ui-stat-grid">
    <x-ui.stat-card label="Total Order" :value="number_format($totalOrder, 0, ',', '.')" icon="receipt" description="Seluruh order Anda" />
    <x-ui.stat-card label="Menunggu Validasi" :value="number_format($orderMenunggu, 0, ',', '.')" tone="warning" icon="clock" description="Menunggu pemeriksaan Admin" />
    <x-ui.stat-card label="Dikirim" :value="number_format($orderDikirim, 0, ',', '.')" tone="info" icon="box" description="Order berstatus dikirim" />
    <x-ui.stat-card label="Selesai" :value="number_format($orderSelesai, 0, ',', '.')" tone="success" icon="check" description="Order yang telah selesai" />
</div>
<div class="dashboard-section">
    <x-ui.card title="Kelola Order Anda">
        <p class="page-subtitle mt-0">Periksa detail order atau ubah order yang masih menunggu validasi.</p>
        <div class="page-actions mt-6"><x-ui.button :href="route('order.index')" variant="secondary">Lihat Order</x-ui.button><x-ui.button :href="route('pelanggan.index')" variant="ghost">Data Pelanggan</x-ui.button></div>
    </x-ui.card>
</div>
@endsection
