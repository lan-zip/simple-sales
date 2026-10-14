@extends('layouts.app')
@section('title', 'Tambah Produk')
@section('header', 'Data Produk')
@section('content')
<x-ui.page-header title="Tambah Produk" subtitle="Identitas produk, harga, dan ketersediaan stok." />
@include('produk._form', ['record' => null])
@endsection
