<div class="form-panel">
    <x-ui.validation-summary />
    <x-ui.card>
        <form action="{{ $record ? route('produk.update', $record) : route('produk.store') }}" method="POST" data-submit-state>
            @csrf
            @if($record) @method('PUT') @endif
            <p class="form-note">Kolom bertanda * wajib diisi.</p>
<fieldset class="form-section"><legend>Identitas Produk</legend><div class="form-grid">
    <x-ui.input name="kode_produk" label="Kode Produk" :value="old('kode_produk', $record?->kode_produk)" maxlength="30" placeholder="Contoh: PRD001" required />
    <x-ui.input name="nama_produk" label="Nama Produk" :value="old('nama_produk', $record?->nama_produk)" maxlength="100" required />
    <div class="form-span"><x-ui.input name="kategori_produk" label="Kategori" :value="old('kategori_produk', $record?->kategori_produk)" maxlength="100" required /></div>
</div></fieldset>
<fieldset class="form-section"><legend>Harga dan Stok</legend><div class="form-grid">
    <x-ui.input name="harga_produk" label="Harga (Rp)" type="number" :value="old('harga_produk', $record?->harga_produk)" min="0" step="any" inputmode="decimal" hint="Masukkan angka tanpa pemisah ribuan." required />
    <x-ui.input name="stok_produk" label="Stok" type="number" :value="old('stok_produk', $record?->stok_produk)" min="0" step="1" inputmode="numeric" required />
    <x-ui.input name="satuan_produk" label="Satuan" :value="old('satuan_produk', $record?->satuan_produk)" maxlength="50" placeholder="Contoh: pcs, box, dus" required />
    @if($record)<div><p class="ui-label">Status Produk</p><x-ui.badge :status="$record->status_produk" /></div>@endif
</div></fieldset>
            <x-ui.form-actions :cancel="route('produk.index')" :submit="$record ? 'Simpan Perubahan' : 'Simpan Produk'" />
        </form>
    </x-ui.card>
</div>
