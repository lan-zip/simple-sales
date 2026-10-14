@props(['action', 'value' => '', 'placeholder' => 'Cari di sini…', 'label' => 'Cari data', 'filtered' => false])
<form action="{{ $action }}" method="GET" class="search-toolbar" data-submit-state>
    <x-ui.search-input :value="$value" :label="$label" :placeholder="$placeholder" />
    {{ $slot }}
    <x-ui.button type="submit" loading-text="Mencari…">Cari</x-ui.button>
    @if($value || $filtered)<x-ui.button :href="$action" variant="ghost">Atur Ulang</x-ui.button>@endif
</form>
