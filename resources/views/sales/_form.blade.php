<div class="form-panel">
    <x-ui.validation-summary />
    <x-ui.card>
        <form action="{{ $record ? route('sales.update', $record) : route('sales.store') }}" method="POST" data-submit-state>
            @csrf
            @if($record) @method('PUT') @endif
            <p class="form-note">Kolom bertanda * wajib diisi.</p>
<fieldset class="form-section"><legend>Informasi Akun</legend><div class="form-grid">
    <x-ui.input name="nama_user" label="Nama Sales" :value="old('nama_user', $record?->nama_user)" maxlength="100" autocomplete="name" required />
    <x-ui.input name="username" label="Username" :value="old('username', $record?->username)" maxlength="50" autocomplete="off" autocapitalize="none" spellcheck="false" required />
    <div class="form-span"><x-ui.input name="password" :label="$record ? 'Password Baru' : 'Password'" type="password" autocomplete="new-password" minlength="6" :required="!$record" :hint="$record ? 'Kosongkan jika tidak ingin mengganti password.' : 'Gunakan minimal 6 karakter.'" /></div>
</div></fieldset>
<fieldset class="form-section"><legend>Informasi Kontak</legend><div class="form-grid">
    <x-ui.input name="no_telp" label="Nomor Telepon" type="tel" :value="old('no_telp', $record?->no_telp)" maxlength="20" autocomplete="tel" placeholder="08xxxxxxxxxx" required />
    <div class="form-span"><x-ui.textarea name="alamat" label="Alamat" :value="old('alamat', $record?->alamat)" autocomplete="street-address" required /></div>
</div></fieldset>
            <x-ui.form-actions :cancel="route('sales.index')" :submit="$record ? 'Simpan Perubahan' : 'Simpan Sales'" />
        </form>
    </x-ui.card>
</div>
