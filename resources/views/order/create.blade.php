@extends('layouts.app')
@section('title', 'Tambah Order')
@section('header', 'Order Penjualan')
@section('content')
<x-ui.page-header title="Tambah Order" subtitle="Pilih pelanggan, tambahkan produk, lalu periksa jumlah pesanan." />
@include('order._form', ['record' => null])
@endsection
