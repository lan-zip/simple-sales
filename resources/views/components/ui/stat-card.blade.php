@props(['label', 'value', 'tone' => 'neutral', 'icon' => 'grid', 'description' => null])
@php
    $tone = in_array($tone, ['neutral', 'info', 'success', 'warning', 'danger'], true) ? $tone : 'neutral';
    $displayValue = $value;
    if (str_starts_with((string) $value, 'Rp ')) {
        $amount = (float) str_replace(['Rp ', '.', ','], ['', '', '.'], $value);
        foreach ([1000000000 => 'miliar', 1000000 => 'jt', 1000 => 'rb'] as $divisor => $suffix) {
            if ($amount >= $divisor) {
                $scaled = $amount / $divisor;
                $precision = $scaled < 100 && $scaled != round($scaled) ? 1 : 0;
                $displayValue = 'Rp '.number_format($scaled, $precision, ',', '.').' '.$suffix;
                break;
            }
        }
    }
@endphp
<div {{ $attributes->class(['ui-stat', 'ui-tone-'.$tone]) }}>
    <span class="ui-stat-icon"><x-ui.icon :name="$icon" /></span>
    <dl><dt class="ui-stat-label">{{ $label }}</dt><dd class="ui-stat-value">
        @if($displayValue !== $value)
            <span class="stat-abbreviation" tabindex="0" aria-label="{{ $value }}">{{ $displayValue }}<span class="stat-full-value" aria-hidden="true">{{ $value }}</span></span>
        @else {{ $value }} @endif
    </dd></dl>
    @if($description)<p class="ui-field-hint">{{ $description }}</p>@endif
</div>
