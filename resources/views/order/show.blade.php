@extends('layouts.app')
@section('title', 'Detail Order')
@section('header', 'Order Penjualan')
@section('content')
<x-ui.page-header title="Detail Order" :subtitle="'Rincian pesanan '.$order->nomor_penjualan.'.'">
    <x-slot:badge><x-ui.badge :status="$order->status_penjualan" /></x-slot:badge>
    <x-slot:actions>
        <div class="page-actions no-print">
            @if($order->status_penjualan === 'menunggu_validasi')<x-ui.button :href="route('order.edit', $order)" variant="secondary">Ubah Order</x-ui.button>@endif
            <x-ui.button data-print>Cetak Nota</x-ui.button>
        </div>
    </x-slot:actions>
</x-ui.page-header>
@include('shared.sale-detail', ['sale' => $order, 'isAdmin' => false])
@endsection
