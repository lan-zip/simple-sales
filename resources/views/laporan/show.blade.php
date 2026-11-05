@extends('layouts.app')
@section('title', 'Detail Transaksi Penjualan')
@section('header', 'Laporan Penjualan')
@section('content')
<x-ui.page-header title="Detail Transaksi Penjualan" :subtitle="'Rincian transaksi selesai '.$penjualan->nomor_penjualan.'.'" class="no-print">
    <x-slot:badge><x-ui.badge :status="$penjualan->status_penjualan" /></x-slot:badge>
    <x-slot:actions>
        <div class="page-actions no-print">
            <x-ui.button :href="route('laporan.penjualan')" variant="secondary">Kembali ke Laporan</x-ui.button>
            @if($penjualan->canPrintNota())
                <x-ui.button data-print>Cetak Nota</x-ui.button>
            @endif
        </div>
    </x-slot:actions>
</x-ui.page-header>

@include('shared.sale-detail', ['sale' => $penjualan, 'isAdmin' => false, 'backUrl' => route('laporan.penjualan')])
@endsection
