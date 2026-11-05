@extends('layouts.app')
@section('title', 'Laporan Penjualan')
@section('header', 'Laporan Penjualan')
@section('content')
<x-ui.page-header title="Laporan Penjualan" subtitle="Rekapitulasi transaksi penjualan selesai dan monitoring pendapatan.">
    <x-slot:actions>
        <x-ui.button data-print class="no-print">
            <x-ui.icon name="receipt" />
            <span>Cetak Laporan</span>
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>
<x-ui.validation-summary class="mb-6" />

@php
    $selectedPelanggan = $idPelanggan ? $pelangganList->firstWhere('id_pelanggan', $idPelanggan) : null;
    $pelangganLabel = $selectedPelanggan ? ($selectedPelanggan->nama_pelanggan . ' — ' . $selectedPelanggan->nama_toko) : 'Semua Pelanggan';

    $selectedSales = $idUser ? $salesList->firstWhere('id_user', $idUser) : null;
    $salesLabel = $selectedSales ? $selectedSales->nama_user : 'Semua Sales';

    if ($tanggalMulai && $tanggalSelesai) {
        $periodeLabel = \Carbon\Carbon::parse($tanggalMulai)->locale('id')->translatedFormat('d M Y') . ' s/d ' . \Carbon\Carbon::parse($tanggalSelesai)->locale('id')->translatedFormat('d M Y');
    } elseif ($tanggalMulai) {
        $periodeLabel = 'Mulai ' . \Carbon\Carbon::parse($tanggalMulai)->locale('id')->translatedFormat('d M Y');
    } elseif ($tanggalSelesai) {
        $periodeLabel = 'Sampai ' . \Carbon\Carbon::parse($tanggalSelesai)->locale('id')->translatedFormat('d M Y');
    } else {
        $periodeLabel = 'Semua Periode';
    }
@endphp

<div class="print-brand print-report-header">
    <div class="print-brand-row">
        <div>
            <p class="brand-name">PT. Halus Ciptanadi</p>
            <p class="print-report-subtitle">Sistem Informasi Penjualan</p>
        </div>
        <div class="text-right">
            <h2 class="print-report-title">Laporan Penjualan</h2>
            <p class="print-report-timestamp">Dicetak pada: {{ now()->locale('id')->translatedFormat('d F Y H:i') }}</p>
        </div>
    </div>
    <div class="print-filter-summary">
        <div><strong>Periode:</strong> {{ $periodeLabel }}</div>
        <div><strong>Pelanggan:</strong> {{ $pelangganLabel }}</div>
        <div><strong>Sales:</strong> {{ $salesLabel }}</div>
    </div>
</div>

<div class="ui-stat-grid mb-6">
    <x-ui.stat-card label="Total Pendapatan" tone="success" icon="money" description="Transaksi selesai · sesuai filter aktif">
        <x-slot:value><x-ui.money :value="$totalPendapatan" /></x-slot:value>
    </x-ui.stat-card>
    <x-ui.stat-card label="Total Transaksi Selesai" :value="number_format($totalTransaksi, 0, ',', '.')" icon="receipt" description="Transaksi selesai yang memenuhi kriteria" />
</div>

<div class="mb-6 no-print">
    <x-ui.card title="Filter Laporan">
        <form action="{{ route('laporan.penjualan') }}" method="GET" data-submit-state>
            <div class="form-grid">
                <x-ui.input type="date" name="tanggal_mulai" label="Tanggal Mulai" :value="old('tanggal_mulai', $tanggalMulai)" hint="Batas awal tanggal transaksi." />
                <x-ui.input type="date" name="tanggal_selesai" label="Tanggal Selesai" :value="old('tanggal_selesai', $tanggalSelesai)" hint="Batas akhir tanggal transaksi." />
                <x-ui.select name="id_pelanggan" label="Pelanggan" id="filter-pelanggan" hint="Filter berdasarkan pelanggan.">
                    <option value="">Semua Pelanggan</option>
                    @foreach($pelangganList as $p)
                        <option value="{{ $p->id_pelanggan }}" @selected((string)old('id_pelanggan', $idPelanggan) === (string)$p->id_pelanggan)>{{ $p->nama_pelanggan }} — {{ $p->nama_toko }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="id_user" label="Sales" id="filter-sales" hint="Filter berdasarkan Sales yang mencatat.">
                    <option value="">Semua Sales</option>
                    @foreach($salesList as $s)
                        <option value="{{ $s->id_user }}" @selected((string)old('id_user', $idUser) === (string)$s->id_user)>{{ $s->nama_user }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="form-actions mt-4">
                <x-ui.button type="submit">Terapkan Filter</x-ui.button>
                @if($isFiltered)
                    <x-ui.button :href="route('laporan.penjualan')" variant="ghost">Atur Ulang</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>
</div>

<x-ui.data-table caption="Daftar laporan transaksi penjualan selesai">
    <thead>
        <tr>
            <th scope="col">No.</th>
            <th scope="col">Transaksi / Tanggal</th>
            <th scope="col">Pelanggan / Toko</th>
            <th scope="col">Sales</th>
            <th scope="col" class="numeric">Total</th>
            <th scope="col">Status</th>
            <th scope="col" class="numeric no-print">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transaksi as $item)
            <tr>
                <td>{{ $transaksi->firstItem() + $loop->index }}</td>
                <td>
                    <a class="text-link whitespace-nowrap" href="{{ route('laporan.penjualan.show', $item) }}">{{ $item->nomor_penjualan }}</a>
                    <p class="cell-meta">{{ $item->tanggal_penjualan?->locale('id')->translatedFormat('d M Y') }}</p>
                </td>
                <td class="cell-copy">
                    <p class="font-medium">{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan tidak tersedia' }}</p>
                    <p class="cell-meta">{{ $item->pelanggan?->nama_toko }}</p>
                </td>
                <td>{{ $item->user?->nama_user ?? 'Sales tidak tersedia' }}</td>
                <td class="numeric"><x-ui.money :value="$item->total_penjualan" /></td>
                <td><x-ui.badge :status="$item->status_penjualan" /></td>
                <td class="numeric no-print">
                    <div class="table-actions">
                        <x-ui.button :href="route('laporan.penjualan.show', $item)" variant="secondary" :aria-label="'Detail '.$item->nomor_penjualan">Detail</x-ui.button>
                    </div>
                </td>
            </tr>
        @empty
            <x-ui.list-empty :colspan="7" :filtered="$isFiltered" title="Tidak Ada Data Penjualan" description="Tidak ditemukan transaksi penjualan selesai yang sesuai dengan kriteria filter." :reset="route('laporan.penjualan')" />
        @endforelse
    </tbody>
    <x-slot:pagination>
        <div class="no-print">
            <x-ui.pagination :paginator="$transaksi" />
        </div>
    </x-slot:pagination>
</x-ui.data-table>

<div class="print-report-footer print-only">
    <p>Dicetak melalui Sistem Informasi Penjualan PT. Halus Ciptanadi · Dokumen Internal Direktur</p>
</div>
@endsection
