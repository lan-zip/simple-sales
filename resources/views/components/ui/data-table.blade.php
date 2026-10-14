@props(['caption', 'title' => null])
<div {{ $attributes->class(['ui-card']) }}>
    @if($title)<div class="ui-card-header"><h2 class="ui-panel-title">{{ $title }}</h2>{{ $actions ?? '' }}</div>@endif
    @isset($toolbar)<div class="ui-card-header">{{ $toolbar }}</div>@endisset
    <div class="ui-table-scroll" tabindex="0" role="region" aria-label="{{ $caption }} — geser untuk melihat semua kolom"><table class="ui-table"><caption class="sr-only">{{ $caption }}</caption>{{ $slot }}</table></div>
    @isset($pagination)<div class="ui-card-footer">{{ $pagination }}</div>@endisset
</div>
