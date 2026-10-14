@extends('layouts.app')
@section('title', 'Ubah Produk')
@section('header', 'Data Produk')
@section('content')
<x-ui.page-header title="Ubah Produk" subtitle="Identitas produk, harga, dan ketersediaan stok." />
@include('produk._form', ['record' => $produk])
@endsection
