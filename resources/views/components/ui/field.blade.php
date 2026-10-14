@props(['id', 'label', 'error' => null, 'hint' => null, 'required' => false])
<div class="ui-field">
    <label class="ui-label" for="{{ $id }}">{{ $label }} @if($required)<span aria-hidden="true">*</span><span class="sr-only"> (wajib diisi)</span>@endif</label>
    {{ $slot }}
    @if($hint)<p id="{{ $id }}-hint" class="ui-field-hint">{{ $hint }}</p>@endif
    @if($error)<p id="{{ $id }}-error" class="ui-field-error"><x-ui.icon name="warning" /> {{ $error }}</p>@endif
</div>
