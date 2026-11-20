@props(['hp', 'size' => 'md', 'label' => true])
@php
    $zone = \App\Models\Room::zoneFor($hp);
    $filled = (int) round($hp / 10);
    $fill = ['sehat' => 'bg-primary', 'perhatian' => 'bg-secondary', 'kritis' => 'bg-accent'][$zone];
    $text = ['sehat' => 'text-primary', 'perhatian' => 'text-secondary', 'kritis' => 'text-accent'][$zone];
    $zoneLabel = ['sehat' => 'Sehat', 'perhatian' => 'Perlu perhatian', 'kritis' => 'Kritis'][$zone];
    $height = $size === 'sm' ? 'h-[6px]' : 'h-sm';
@endphp
<div {{ $attributes->class(['flex items-center', $size === 'sm' ? 'gap-sm' : 'gap-md']) }}>
    <div @class(['grid grid-cols-10 gap-[2px]', 'flex-1' => $size !== 'sm', 'w-[80px]' => $size === 'sm']) role="img" aria-label="HP {{ $hp }} dari 100, {{ strtolower($zoneLabel) }}">
        @for ($i = 0; $i < 10; $i++)
            <span @class([$height, 'rounded-[1px]', $i < $filled ? $fill : 'bg-meter-empty'])></span>
        @endfor
    </div>
    <span @class(['whitespace-nowrap font-code tabular-nums', $size === 'sm' ? 'text-code-sm' : 'text-code', $zone === 'sehat' ? 'text-on-surface' : $text])>{{ $hp }}/100{{ $size === 'sm' ? '' : ' HP' }}</span>
    @if ($label && $size !== 'sm')
        <span @class(['font-label text-label', $text])>{{ $zoneLabel }}</span>
    @endif
</div>
