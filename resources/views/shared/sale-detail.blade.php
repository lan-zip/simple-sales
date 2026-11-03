<x-ui.validation-summary />
<div class="detail-stack">
    <div class="print-brand"><p class="brand-name">PT. Halus Ciptanadi</p><p>Pencatatan Penjualan · Nota Order</p></div>
    <x-ui.card title="Informasi Penjualan">
        <dl class="detail-grid">
            <div><dt>Nomor {{ $isAdmin ? 'Transaksi' : 'Order' }}</dt><dd>{{ $sale->nomor_penjualan }}</dd></div>
            <div><dt>Tanggal Order</dt><dd>{{ $sale->tanggal_penjualan?->locale('id')->translatedFormat('d F Y') }}</dd></div>
            @if($isAdmin)<div><dt>Sales</dt><dd>{{ $sale->user?->nama_user ?? 'Sales tidak tersedia' }}</dd></div>@endif
            <div><dt>Status</dt><dd><x-ui.badge :status="$sale->status_penjualan" /></dd></div>
            @if($sale->tanggal_validasi)<div><dt>Tanggal Validasi</dt><dd>{{ $sale->tanggal_validasi?->locale('id')->translatedFormat('d F Y') }}</dd></div>@endif
            @if($sale->tanggal_selesai)<div><dt>Tanggal Selesai</dt><dd>{{ $sale->tanggal_selesai?->locale('id')->translatedFormat('d F Y') }}</dd></div>@endif
            <div><dt>Pelanggan</dt><dd>{{ $sale->pelanggan?->nama_pelanggan ?? 'Pelanggan tidak tersedia' }}</dd></div>
            <div><dt>Nama Toko</dt><dd>{{ $sale->pelanggan?->nama_toko ?? '—' }}</dd></div>
            <div><dt>Nomor WhatsApp Pelanggan</dt><dd>{{ $sale->pelanggan?->no_wa ?? '—' }}</dd></div>
            <div class="form-span"><dt>Alamat Pelanggan</dt><dd>{{ $sale->pelanggan?->alamat_pelanggan ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>
    <x-ui.data-table caption="Rincian produk pada penjualan" title="Rincian Produk">
        <thead><tr><th scope="col">Produk</th><th scope="col" class="numeric">Harga Satuan</th><th scope="col" class="numeric">Jumlah</th><th scope="col" class="numeric">Subtotal</th></tr></thead>
        <tbody>
            @forelse($sale->detailPenjualan as $detail)
                <tr>
                    <td class="cell-copy"><p class="font-medium">{{ $detail->produk?->nama_produk ?? 'Produk tidak tersedia' }}</p><p class="cell-meta">{{ $detail->produk?->kode_produk }}</p></td>
                    <td class="numeric"><x-ui.money :value="$detail->harga_satuan" /></td>
                    <td class="numeric">{{ number_format($detail->jumlah_produk, 0, ',', '.') }} {{ $detail->produk?->satuan_produk }}</td>
                    <td class="numeric"><x-ui.money :value="$detail->subtotal" /></td>
                </tr>
            @empty
                <tr><td colspan="4"><x-ui.empty-state title="Rincian Produk Tidak Tersedia" description="Tidak ada rincian produk yang dapat ditampilkan untuk penjualan ini." /></td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><th scope="row" colspan="3" class="numeric">Total Penjualan</th><td class="numeric font-bold"><x-ui.money :value="$sale->total_penjualan" /></td></tr></tfoot>
    </x-ui.data-table>
    @if($sale->foto_bukti_penyerahan)
        <x-ui.card title="Bukti Penyerahan">
            <div class="space-y-3">
                <a href="{{ asset('storage/' . $sale->foto_bukti_penyerahan) }}" target="_blank" rel="noopener noreferrer" class="inline-block">
                    <img src="{{ asset('storage/' . $sale->foto_bukti_penyerahan) }}" alt="Foto Bukti Penyerahan {{ $sale->nomor_penjualan }}" class="rounded-lg border border-gray-200 shadow-sm max-w-full" style="max-height: 360px; object-fit: contain;">
                </a>
                <p class="cell-meta">Dokumentasi penyerahan pesanan kepada customer.</p>
            </div>
        </x-ui.card>
    @endif
</div>
<div class="page-actions mt-6 no-print"><x-ui.button :href="route($isAdmin ? 'transaksi.index' : 'order.index')" variant="secondary">Kembali ke Daftar</x-ui.button></div>
