@props(['title', 'description', 'icon' => 'box'])
<div {{ $attributes->class(['ui-empty-state']) }}><x-ui.icon :name="$icon" /><h3>{{ $title }}</h3><p>{{ $description }}</p>@isset($action)<div>{{ $action }}</div>@endisset</div>
