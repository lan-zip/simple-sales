@foreach(['success' => 'Berhasil', 'warning' => 'Perhatian', 'error' => 'Terjadi Kesalahan', 'info' => 'Informasi'] as $type => $title)
    @if(session()->has($type))
        <x-ui.alert :type="$type" :title="$title" class="mb-6">{{ session($type) }}</x-ui.alert>
    @endif
@endforeach
