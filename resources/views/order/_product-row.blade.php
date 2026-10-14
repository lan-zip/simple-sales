@php
    $selectedProduct = is_scalar($row['id_produk'] ?? '') ? (string) ($row['id_produk'] ?? '') : '';
    $quantity = is_scalar($row['jumlah'] ?? '') ? ($row['jumlah'] ?? '') : '';
    $unavailable = $selectedProduct !== '' && !$produk->contains(fn ($item) => (string) $item->id_produk === $selectedProduct);
@endphp
<fieldset class="order-product-row" data-product-row data-row-index="{{ $index }}">
    <legend class="sr-only">Produk pesanan</legend>
    <div class="order-product-select">
        <x-ui.select :name="'produk['.$index.'][id_produk]'" :id="'product-'.$index" label="Produk" :error="$errors->first('produk.'.($errorIndex ?? $index).'.id_produk')" data-product-select required>
            <option value="">Pilih produk</option>
            @if($unavailable)<option value="{{ $selectedProduct }}" selected>Produk #{{ $selectedProduct }} tidak tersedia — pilih kembali</option>@endif
            @foreach($produk as $item)
                <option value="{{ $item->id_produk }}" data-price="{{ $item->harga_produk }}" data-stock="{{ $item->stok_produk }}" data-unit="{{ $item->satuan_produk }}" @selected($selectedProduct === (string) $item->id_produk)>{{ $item->nama_produk }} · {{ $item->kode_produk }} · Stok {{ $item->stok_produk }} {{ $item->satuan_produk }} · Rp {{ number_format($item->harga_produk, (float) $item->harga_produk == round((float) $item->harga_produk) ? 0 : 2, ',', '.') }}</option>
            @endforeach
        </x-ui.select>
        <p class="ui-field-hint" data-product-stock></p>
    </div>
    <x-ui.input :name="'produk['.$index.'][jumlah]'" :id="'quantity-'.$index" label="Jumlah" type="number" :value="$quantity" :error="$errors->first('produk.'.($errorIndex ?? $index).'.jumlah')" min="1" step="1" inputmode="numeric" data-product-quantity required />
    <div class="order-line-price"><p class="ui-label">Harga Satuan</p><output data-product-price>—</output></div>
    <div class="order-line-subtotal"><p class="ui-label">Subtotal</p><output data-product-subtotal>—</output></div>
    <x-ui.button variant="ghost" data-remove-product aria-label="Hapus baris produk">Hapus</x-ui.button>
</fieldset>
