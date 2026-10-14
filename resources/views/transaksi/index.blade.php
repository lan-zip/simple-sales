@extends('layouts.app')
@section('title', 'Transaksi Penjualan')
@section('header', 'Transaksi Penjualan')
@section('content')
<x-ui.page-header title="Transaksi Penjualan" subtitle="Tinjau order dari Sales dan validasi transaksi yang menunggu pemeriksaan." />
<x-ui.validation-summary />
@include('shared.sales-list', ['records' => $transaksi, 'isAdmin' => true, 'routePrefix' => 'transaksi'])
@endsection
