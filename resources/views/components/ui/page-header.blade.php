@props(['title', 'subtitle'])
<div {{ $attributes->class(['page-header']) }}>
    <div class="page-header-copy"><div class="page-title-row"><h1 class="page-title">{{ $title }}</h1>{{ $badge ?? '' }}</div><p class="page-subtitle">{{ $subtitle }}</p></div>
    @isset($actions)<div class="page-actions">{{ $actions }}</div>@endisset
</div>
