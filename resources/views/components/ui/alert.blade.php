@props(['type' => 'info', 'title' => null])
@php
    $tone = match ($type) { 'success' => 'success', 'warning' => 'warning', 'error', 'danger' => 'danger', default => 'info' };
    $icon = match ($tone) { 'success' => 'check', 'warning', 'danger' => 'warning', default => 'info' };
@endphp
<div {{ $attributes->class(['ui-alert', 'ui-tone-'.$tone]) }} role="{{ $tone === 'danger' ? 'alert' : 'status' }}"><x-ui.icon :name="$icon" /><div>@if($title)<p class="ui-alert-title">{{ $title }}</p>@endif{{ $slot }}</div></div>
