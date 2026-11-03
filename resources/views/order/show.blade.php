@extends('layouts.app')
@section('title', 'Detail Order')
@section('header', 'Order Penjualan')
@section('content')
<x-ui.page-header title="Detail Order" :subtitle="'Rincian pesanan '.$order->nomor_penjualan.'.'">
    <x-slot:badge><x-ui.badge :status="$order->status_penjualan" /></x-slot:badge>
    <x-slot:actions>
        <div class="page-actions no-print">
            @if($order->isMenungguValidasi())<x-ui.button :href="route('order.edit', $order)" variant="secondary">Ubah Order</x-ui.button>@endif
            @if($order->isDikirim() && $order->foto_bukti_penyerahan)
                <x-ui.record-action :action="route('order.selesai', $order)" message="Selesaikan order ini? Status akan diperbarui menjadi selesai dan transaksi dianggap tuntas." label="Selesaikan Order" variant="primary" />
            @endif
            <x-ui.button data-print>Cetak Nota</x-ui.button>
        </div>
    </x-slot:actions>
</x-ui.page-header>

@if($order->isDikirim())
    <div class="mb-6">
        <x-ui.card title="Unggah Bukti Penyerahan">
            <p class="ui-field-hint mb-4">
                Pesanan sedang dalam tahap pengiriman. Unggah foto penyerahan pesanan kepada customer sebelum menyelesaikan order.
            </p>
            @if($order->foto_bukti_penyerahan)
                <x-ui.alert type="success" class="mb-4">
                    Foto bukti penyerahan telah tersimpan. Anda dapat memperbaruinya di bawah atau menyelesaikan order menggunakan tombol di atas.
                </x-ui.alert>
            @else
                <x-ui.alert type="warning" class="mb-4">
                    Foto bukti penyerahan wajib diunggah agar order dapat diselesaikan.
                </x-ui.alert>
            @endif
            <form action="{{ route('order.uploadBukti', $order) }}" method="POST" enctype="multipart/form-data" class="detail-stack" data-submit-state>
                @csrf
                <x-ui.input type="file" name="foto_bukti_penyerahan" label="Foto Bukti Penyerahan" hint="Format JPG, PNG, atau WebP (maksimal 5MB)." accept="image/jpeg,image/png,image/webp" required />
                <div class="page-actions">
                    <x-ui.button type="submit" variant="secondary">
                        <x-ui.icon name="check" /> {{ $order->foto_bukti_penyerahan ? 'Perbarui Bukti Penyerahan' : 'Simpan Bukti Penyerahan' }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endif

@include('shared.sale-detail', ['sale' => $order, 'isAdmin' => false])
@endsection
