@extends('layouts.app')
@section('title', 'Ubah Pelanggan')
@section('header', 'Data Pelanggan')
@section('content')
<x-ui.page-header title="Ubah Pelanggan" subtitle="Identitas pelanggan, toko, dan kontak WhatsApp." />
@include('pelanggan._form', ['record' => $pelanggan])
@endsection
