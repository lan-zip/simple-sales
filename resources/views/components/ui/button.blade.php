@props(['variant' => 'primary', 'type' => 'button', 'href' => null, 'disabled' => false, 'loadingText' => 'Memproses…'])
@php($variant = in_array($variant, ['primary', 'secondary', 'ghost', 'danger'], true) ? $variant : 'primary')
@if($href !== null)
    <a @if(!$disabled) href="{{ $href }}" @else aria-disabled="true" tabindex="-1" @endif {{ $attributes->class(['ui-button', 'ui-button--'.$variant]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @disabled($disabled) data-loading-text="{{ $loadingText }}" {{ $attributes->class(['ui-button', 'ui-button--'.$variant]) }}>
        <span class="ui-spinner" data-button-spinner hidden aria-hidden="true"></span>
        <span data-button-label>{{ $slot }}</span>
    </button>
@endif
