@props(['id', 'title'])
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title" {{ $attributes->class(['ui-modal']) }}>
    <div class="ui-modal-header"><h2 id="{{ $id }}-title">{{ $title }}</h2><button type="button" class="icon-button" data-dialog-close aria-label="Tutup dialog"><x-ui.icon name="x" /></button></div>
    <div class="ui-modal-body">{{ $slot }}</div>
    @isset($actions)<div class="ui-modal-actions">{{ $actions }}</div>@endisset
</dialog>
