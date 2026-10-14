@props(['href', 'active' => false, 'icon' => 'grid'])
<a href="{{ $href }}" {{ $attributes->class(['sidebar-item', 'is-active' => $active]) }} @if($active) aria-current="page" @endif><x-ui.icon :name="$icon" /><span>{{ $slot }}</span></a>
