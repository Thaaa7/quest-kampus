@props(['value', 'max' => 10, 'color' => 'bg-primary', 'label'])
<div {{ $attributes->class('grid gap-[2px]') }} style="grid-template-columns: repeat({{ $max }}, minmax(0, 1fr))" role="img" aria-label="{{ $label }}">
    @for ($i = 0; $i < $max; $i++)
        <span @class(['h-xs rounded-[1px]', $i < min($value, $max) ? $color : 'bg-meter-empty'])></span>
    @endfor
</div>
