@props(['status'])
@php
    [$label, $tone, $icon] = match ($status) {
        'menunggu_validasi' => ['Menunggu Validasi', 'warning', 'clock'],
        'tervalidasi' => ['Tervalidasi', 'info', 'check'],
        'diproses' => ['Diproses', 'info', 'clock'],
        'dikirim' => ['Dikirim', 'info', 'box'],
        'selesai' => ['Selesai', 'success', 'check'],
        'aktif' => ['Aktif', 'success', 'check'],
        'nonaktif' => ['Nonaktif', 'neutral', 'info'],
        default => [ucfirst(str_replace('_', ' ', $status ?: 'Tidak diketahui')), 'neutral', 'info'],
    };
@endphp
<span {{ $attributes->class(['ui-badge', 'ui-tone-'.$tone]) }}><x-ui.icon :name="$icon" />{{ $label }}</span>
