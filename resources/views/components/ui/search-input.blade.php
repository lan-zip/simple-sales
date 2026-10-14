@props(['name' => 'cari', 'id' => 'page-search', 'label' => 'Cari data', 'value' => '', 'placeholder' => 'Cari di sini…'])
<div class="ui-search"><label for="{{ $id }}" class="sr-only">{{ $label }}</label><x-ui.icon name="search" /><input type="search" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}" {{ $attributes->class(['ui-input']) }}></div>
