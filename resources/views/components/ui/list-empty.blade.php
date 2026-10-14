@props(['colspan', 'filtered' => false, 'title', 'description', 'create' => null, 'createLabel' => 'Tambah Data', 'reset'])
<tr><td colspan="{{ $colspan }}">
    <x-ui.empty-state :title="$filtered ? 'Tidak Ada Hasil Pencarian' : $title" :description="$filtered ? 'Coba kata kunci lain atau atur ulang pencarian.' : $description">
        <x-slot:action>
            @if($filtered)<x-ui.button :href="$reset" variant="secondary">Atur Ulang Pencarian</x-ui.button>
            @elseif($create)<x-ui.button :href="$create">{{ $createLabel }}</x-ui.button>@endif
        </x-slot:action>
    </x-ui.empty-state>
</td></tr>
