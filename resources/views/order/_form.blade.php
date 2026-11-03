@php
    $rows = old('produk', isset($detailOrder) ? $detailOrder->toArray() : [['id_produk' => '', 'jumlah' => '']]);
    $rows = is_array($rows) && count($rows) ? $rows : [['id_produk' => '', 'jumlah' => '']];
    $selectedCustomer = old('id_pelanggan', $record?->id_pelanggan);
    $selectedCustomer = is_scalar($selectedCustomer) ? (string) $selectedCustomer : '';
    $customerUnavailable = $selectedCustomer !== '' && !$pelanggan->contains(fn ($item) => (string) $item->id_pelanggan === $selectedCustomer);
@endphp
<x-ui.validation-summary />
<form action="{{ $record ? route('order.update', $record) : route('order.store') }}" method="POST" enctype="multipart/form-data" class="detail-stack" data-order-form data-submit-state>
    @csrf
    @if($record) @method('PUT') @endif
    <x-ui.card title="Informasi Pelanggan">
        <div class="form-panel">
            <x-ui.select name="id_pelanggan" label="Pelanggan" :hint="'Pilih pelanggan dan toko tujuan pesanan.'" required>
                <option value="">Pilih pelanggan</option>
                @if($customerUnavailable)<option value="{{ $selectedCustomer }}" selected>Pelanggan #{{ $selectedCustomer }} tidak tersedia — pilih kembali</option>@endif
                @foreach($pelanggan as $item)
                    <option value="{{ $item->id_pelanggan }}" @selected($selectedCustomer === (string) $item->id_pelanggan)>{{ $item->nama_pelanggan }} — {{ $item->nama_toko }}</option>
                @endforeach
            </x-ui.select>
            @if($pelanggan->isEmpty())
                <x-ui.alert type="warning" class="mt-4">Belum ada pelanggan aktif. <a class="text-link" href="{{ route('pelanggan.create') }}">Tambah Pelanggan</a> sebelum mencatat order.</x-ui.alert>
            @endif
        </div>
    </x-ui.card>
    <x-ui.card title="Dokumentasi Toko">
        <div class="form-panel">
            <x-ui.input
                type="file"
                name="foto_toko"
                label="Foto Toko"
                :hint="$record ? 'Unggah foto baru jika ingin mengganti dokumentasi toko saat ini (maksimal 5MB, format JPG, PNG, atau WebP).' : 'Unggah foto toko pelanggan sebagai dokumentasi pesanan (wajib diisi, maksimal 5MB, format JPG, PNG, atau WebP).'"
                accept="image/jpeg,image/png,image/jpg,image/webp"
                :required="!$record"
            />
            @if($record?->foto_toko)
                <div class="mt-4 p-4 rounded-lg border border-gray-200 bg-gray-50 flex items-start gap-4">
                    <img src="{{ asset('storage/' . $record->foto_toko) }}" alt="Foto Toko Saat Ini" class="w-24 h-24 object-cover rounded border border-gray-300">
                    <div>
                        <p class="font-medium text-sm">Foto toko saat ini telah tersimpan.</p>
                        <p class="cell-meta">Biarkan kolom foto kosong jika tidak ingin mengubah foto toko.</p>
                        <a href="{{ asset('storage/' . $record->foto_toko) }}" target="_blank" rel="noopener noreferrer" class="text-link text-sm mt-1 inline-block">Lihat Foto Ukuran Penuh &rarr;</a>
                    </div>
                </div>
            @endif
        </div>
    </x-ui.card>
    <x-ui.card title="Produk Pesanan">
        <x-slot:actions><x-ui.button variant="secondary" data-add-product :disabled="$produk->isEmpty()"><x-ui.icon name="plus" /> Tambah Produk</x-ui.button></x-slot:actions>
        @if($produk->isEmpty())<x-ui.alert type="warning" class="mb-6">Tidak ada produk aktif dengan stok tersedia. Hubungi Admin untuk memeriksa katalog.</x-ui.alert>@endif
        <div class="order-product-list" data-product-list>
            @foreach($rows as $originalIndex => $row)
                @include('order._product-row', ['index' => $loop->index, 'errorIndex' => $originalIndex, 'row' => is_array($row) ? $row : []])
            @endforeach
        </div>
        <template data-product-template>@include('order._product-row', ['index' => '__INDEX__', 'errorIndex' => '__INDEX__', 'row' => []])</template>
        <p class="ui-field-hint" role="status" data-order-feedback></p>
        <div class="order-summary" data-order-summary hidden>
            <span>Estimasi Total</span><output class="order-total" data-order-total aria-live="polite" aria-atomic="true">—</output>
            <p class="ui-field-hint">Berdasarkan harga produk saat ini. Total akhir diperiksa kembali saat order disimpan.</p>
        </div>
        <noscript><p class="ui-field-hint">Aktifkan JavaScript untuk menambah baris dan melihat estimasi total. Baris yang tersedia tetap dapat diisi dan disimpan.</p></noscript>
        <x-ui.form-actions :cancel="route('order.index')" :submit="$record ? 'Simpan Perubahan' : 'Simpan Order'" :disabled="$produk->isEmpty() || $pelanggan->isEmpty()" />
    </x-ui.card>
</form>
