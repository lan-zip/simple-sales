@props(['title' => 'Data Belum Dapat Ditampilkan', 'description' => 'Silakan coba kembali.'])
<x-ui.alert type="error" :title="$title" {{ $attributes }}><p>{{ $description }}</p>@isset($action)<div class="mt-4">{{ $action }}</div>@endisset</x-ui.alert>
