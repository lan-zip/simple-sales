@extends('layouts.app')
@section('title', 'Ubah Order')
@section('header', 'Order Penjualan')
@section('content')
<x-ui.page-header title="Ubah Order" :subtitle="'Ubah rincian pesanan '.$order->nomor_penjualan.' yang masih menunggu validasi.'" />
@include('order._form', ['record' => $order])
@endsection
