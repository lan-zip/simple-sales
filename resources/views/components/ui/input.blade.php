@props(['name', 'label', 'id' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false, 'error' => null])
@php
    $id ??= 'field-'.trim(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $name), '-');
    $error ??= $errors->first(str_replace(['[', ']'], ['.', ''], $name));
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error ' : '').$attributes->get('aria-describedby', ''));
@endphp
<x-ui.field :id="$id" :label="$label" :hint="$hint" :error="$error" :required="$required">
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" data-required-message="{{ $label }} wajib diisi." @if($type !== 'password') value="{{ $value }}" @endif @required($required)
        @if($error) aria-invalid="true" @endif @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('aria-describedby')->class(['ui-input', 'is-invalid' => (bool) $error]) }}>
</x-ui.field>
