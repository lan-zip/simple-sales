@extends('layouts.app')
@section('title', 'Tambah Pelanggan')
@section('header', 'Data Pelanggan')
@section('content')
<x-ui.page-header title="Tambah Pelanggan" subtitle="Identitas pelanggan, toko, dan kontak WhatsApp." />
@include('pelanggan._form', ['record' => null])
@endsection
