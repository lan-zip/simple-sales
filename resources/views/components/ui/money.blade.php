@props(['value'])
@php($decimals = round((float) $value, 2) == round((float) $value) ? 0 : 2)
<span {{ $attributes->class(['money-value']) }}>Rp {{ number_format((float) $value, $decimals, ',', '.') }}</span>
