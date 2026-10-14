@props(['type' => 'info', 'title' => null])
<x-ui.alert :type="$type" :title="$title" {{ $attributes->class(['ui-toast']) }}>{{ $slot }}</x-ui.alert>
