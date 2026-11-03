@props(['status'])
@php
    $statusValue = $status instanceof \App\Enums\OrderStatus ? $status->value : (string) $status;
    [$label, $tone, $icon] = match ($statusValue) {
        'menunggu_validasi' => ['Menunggu Validasi', 'warning', 'clock'],
        'tervalidasi' => ['Tervalidasi', 'info', 'check'],
        'diproses' => ['Diproses', 'info', 'clock'],
        'dikirim' => ['Dikirim', 'info', 'box'],
        'selesai' => ['Selesai', 'success', 'check'],
        'aktif' => ['Aktif', 'success', 'check'],
        'nonaktif' => ['Nonaktif', 'neutral', 'info'],
        default => [ucfirst(str_replace('_', ' ', $statusValue ?: 'Tidak diketahui')), 'neutral', 'info'],
    };
@endphp
<span {{ $attributes->class(['ui-badge', 'ui-tone-'.$tone]) }}><x-ui.icon :name="$icon" />{{ $label }}</span>
