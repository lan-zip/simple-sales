<x-ui.data-table :caption="$isAdmin ? 'Daftar transaksi penjualan' : 'Daftar order Anda'">
    <x-slot:toolbar>
        <x-ui.search-toolbar :action="route($routePrefix.'.index')" :value="$cari" :filtered="$isAdmin && (bool) ($status ?? '')" :placeholder="$isAdmin ? 'Cari nomor transaksi, pelanggan, toko, atau sales…' : 'Cari nomor order, pelanggan, atau toko…'" label="Cari penjualan">
            @if($isAdmin)
                <x-ui.select name="status" label="Status Transaksi" id="filter-status">
                    <option value="">Semua Status</option>
                    @foreach(['menunggu_validasi' => 'Menunggu Validasi', 'tervalidasi' => 'Tervalidasi', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                    @if($status && !in_array($status, ['menunggu_validasi', 'tervalidasi', 'diproses', 'dikirim', 'selesai'], true))
                        <option value="{{ $status }}" selected>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endif
                </x-ui.select>
            @endif
        </x-ui.search-toolbar>
    </x-slot:toolbar>
    <thead><tr>
        <th scope="col">No.</th><th scope="col">{{ $isAdmin ? 'Transaksi' : 'Order' }} / Tanggal</th><th scope="col">Pelanggan / Toko</th>
        @if($isAdmin)<th scope="col">Sales</th>@endif
        <th scope="col" class="numeric">Total</th><th scope="col">Status</th><th scope="col" class="numeric">Aksi</th>
    </tr></thead>
    <tbody>
        @forelse($records as $item)
            <tr>
                <td>{{ $records->firstItem() + $loop->index }}</td>
                <td><a class="text-link whitespace-nowrap" href="{{ route($routePrefix.'.show', $item) }}">{{ $item->nomor_penjualan }}</a><p class="cell-meta">{{ $item->tanggal_penjualan?->locale('id')->translatedFormat('d M Y') }}</p></td>
                <td class="cell-copy"><p class="font-medium">{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan tidak tersedia' }}</p><p class="cell-meta">{{ $item->pelanggan?->nama_toko }}</p></td>
                @if($isAdmin)<td>{{ $item->user?->nama_user ?? 'Sales tidak tersedia' }}</td>@endif
                <td class="numeric"><x-ui.money :value="$item->total_penjualan" /></td>
                <td><x-ui.badge :status="$item->status_penjualan" /></td>
                <td><div class="table-actions">
                    <x-ui.button :href="route($routePrefix.'.show', $item)" variant="secondary" :aria-label="'Detail '.$item->nomor_penjualan">Detail</x-ui.button>
                    @if(!$isAdmin && $item->status_penjualan === 'menunggu_validasi')
                        <x-ui.button :href="route('order.edit', $item)" variant="ghost" :aria-label="'Ubah '.$item->nomor_penjualan">Ubah</x-ui.button>
                    @endif
                </div></td>
            </tr>
        @empty
            <x-ui.list-empty :colspan="$isAdmin ? 7 : 6" :filtered="(bool) $cari || ($isAdmin && (bool) ($status ?? ''))" :title="$isAdmin ? 'Belum Ada Transaksi' : 'Belum Ada Order'" :description="$isAdmin ? 'Order dari Sales akan muncul di sini untuk diperiksa.' : 'Catat order pertama Anda dengan memilih pelanggan dan produk.'" :create="$isAdmin ? null : route('order.create')" create-label="Tambah Order" :reset="route($routePrefix.'.index')" />
        @endforelse
    </tbody>
    <x-slot:pagination><x-ui.pagination :paginator="$records" /></x-slot:pagination>
</x-ui.data-table>
