@props(['label' => 'Memuat data…'])
<div {{ $attributes->class(['ui-loading']) }} role="status"><span class="ui-spinner" aria-hidden="true"></span><span>{{ $label }}</span></div>
