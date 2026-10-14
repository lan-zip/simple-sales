<div class="form-panel">
    <x-ui.validation-summary />
    <x-ui.card>
        <form action="{{ $record ? route('pelanggan.update', $record) : route('pelanggan.store') }}" method="POST" data-submit-state>
            @csrf
            @if($record) @method('PUT') @endif
            <p class="form-note">Kolom bertanda * wajib diisi.</p>
<fieldset class="form-section"><legend>Identitas Pelanggan</legend><div class="form-grid">
    <x-ui.input name="kode_pelanggan" label="Kode Pelanggan" :value="old('kode_pelanggan', $record?->kode_pelanggan)" maxlength="30" placeholder="Contoh: PLG001" required />
    <x-ui.input name="nama_pelanggan" label="Nama Pelanggan" :value="old('nama_pelanggan', $record?->nama_pelanggan)" maxlength="100" autocomplete="name" required />
    <div class="form-span"><x-ui.input name="nama_toko" label="Nama Toko" :value="old('nama_toko', $record?->nama_toko)" maxlength="100" autocomplete="organization" required /></div>
</div></fieldset>
<fieldset class="form-section"><legend>Informasi Kontak</legend><div class="form-grid">
    <x-ui.input name="no_wa" label="Nomor WhatsApp" type="tel" :value="old('no_wa', $record?->no_wa)" maxlength="20" autocomplete="tel" placeholder="08xxxxxxxxxx" hint="Pastikan nomor dapat menerima nota pesanan." required />
    <div class="form-span"><x-ui.textarea name="alamat_pelanggan" label="Alamat Pelanggan" :value="old('alamat_pelanggan', $record?->alamat_pelanggan)" autocomplete="street-address" required /></div>
</div></fieldset>
            <x-ui.form-actions :cancel="route('pelanggan.index')" :submit="$record ? 'Simpan Perubahan' : 'Simpan Pelanggan'" />
        </form>
    </x-ui.card>
</div>
