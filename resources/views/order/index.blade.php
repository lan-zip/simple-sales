@extends('layouts.app')
@section('title', 'Order Penjualan')
@section('header', 'Order Penjualan')
@section('content')
<x-ui.page-header title="Order Penjualan" subtitle="Pantau order Anda dan ubah pesanan yang masih menunggu validasi.">
    <x-slot:actions><x-ui.button :href="route('order.create')"><x-ui.icon name="plus" /> Tambah Order</x-ui.button></x-slot:actions>
</x-ui.page-header>
<x-ui.validation-summary />
@include('shared.sales-list', ['records' => $order, 'isAdmin' => false, 'routePrefix' => 'order'])
@endsection
