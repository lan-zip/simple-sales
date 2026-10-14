@props(['action', 'message', 'label', 'variant' => 'danger'])
<form action="{{ $action }}" method="POST" data-submit-state data-confirm-message="{{ $message }}" data-confirm-label="{{ $label }}" data-confirm-variant="{{ $variant }}">
    @csrf
    @method('PATCH')
    <x-ui.button type="submit" :variant="$variant">{{ $label }}</x-ui.button>
</form>
