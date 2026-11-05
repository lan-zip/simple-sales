@props(['action', 'message', 'label', 'variant' => 'danger', 'method' => 'PATCH'])
<form action="{{ $action }}" method="POST" {{ $attributes }} data-submit-state data-confirm-message="{{ $message }}" data-confirm-label="{{ $label }}" data-confirm-variant="{{ $variant }}">
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method($method)
    @endif
    <x-ui.button type="submit" :variant="$variant">{{ $label }}</x-ui.button>
</form>
