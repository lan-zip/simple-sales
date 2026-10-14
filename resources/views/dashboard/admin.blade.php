@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('header', 'Halaman Utama')
@section('content')
<x-ui.page-header title="Halaman Operasional" subtitle="Ringkasan data aktif, pendapatan, dan transaksi yang menunggu validasi." />
<div class="ui-stat-grid">
    <x-ui.stat-card label="Total Sales" :value="number_format($totalSales, 0, ',', '.')" icon="users" description="Sales aktif" />
    <x-ui.stat-card label="Total Pelanggan" :value="number_format($totalPelanggan, 0, ',', '.')" tone="info" icon="user" description="Pelanggan aktif" />
    <x-ui.stat-card label="Total Pendapatan" :value="'Rp '.number_format($totalPendapatan, (float) $totalPendapatan == round((float) $totalPendapatan) ? 0 : 2, ',', '.')" tone="success" icon="money" description="Transaksi selesai · seluruh periode" />
    <x-ui.stat-card label="Transaksi Pending" :value="number_format($transaksiMenunggu, 0, ',', '.')" tone="warning" icon="clock" description="Menunggu validasi" />
</div>
<section class="dashboard-section" aria-labelledby="activity-title">
    <h2 id="activity-title" class="section-title">Aktivitas Terbaru</h2>
    <x-ui.data-table caption="Lima order sales terbaru" title="Order Sales Terbaru">
        <x-slot:actions><a class="text-link" href="{{ route('transaksi.index') }}">Lihat Semua <x-ui.icon name="arrow-right" /></a></x-slot:actions>
        <thead><tr><th scope="col">Nomor Order</th><th scope="col">Pelanggan</th><th scope="col">Sales / Tanggal</th><th scope="col">Status</th><th scope="col" class="numeric">Total</th></tr></thead>
        <tbody>
            @forelse($orderTerbaru as $item)
                <tr>
                    <td><a class="text-link whitespace-nowrap" href="{{ route('transaksi.show', $item) }}">{{ $item->nomor_penjualan }}</a></td>
                    <td class="cell-copy">{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan tidak tersedia' }}<p class="cell-meta">{{ $item->pelanggan?->nama_toko }}</p></td>
                    <td>{{ $item->user?->nama_user ?? 'Sales tidak tersedia' }}<p class="cell-meta">{{ $item->tanggal_penjualan?->locale('id')->translatedFormat('d M Y') }}</p></td>
                    <td><x-ui.badge :status="$item->status_penjualan" /></td>
                    <td class="numeric"><x-ui.money :value="$item->total_penjualan" /></td>
                </tr>
            @empty
                <x-ui.list-empty colspan="5" title="Belum Ada Order" description="Order yang dicatat Sales akan ditampilkan di sini." :reset="route('transaksi.index')" />
            @endforelse
        </tbody>
    </x-ui.data-table>
</section>
@endsection
