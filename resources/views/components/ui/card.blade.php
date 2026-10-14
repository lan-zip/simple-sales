@props(['title' => null])
<section {{ $attributes->class(['ui-card']) }}>
    @if($title)<div class="ui-card-header"><h2 class="ui-panel-title">{{ $title }}</h2>{{ $actions ?? '' }}</div>@endif
    <div class="ui-card-body">{{ $slot }}</div>
    @isset($footer)<div class="ui-card-footer">{{ $footer }}</div>@endisset
</section>
