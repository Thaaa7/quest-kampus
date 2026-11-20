@props(['user' => null, 'initials' => null, 'size' => 'md'])
@php($text = $initials ?? $user?->initials() ?? '?')
<span {{ $attributes->class(['inline-flex shrink-0 items-center justify-center rounded-full bg-primary-container font-label text-primary', 'h-[40px] w-[40px] text-label' => $size === 'md', 'h-[28px] w-[28px] text-caption' => $size === 'sm', 'h-[56px] w-[56px] text-title' => $size === 'lg']) }} aria-hidden="true">{{ $text }}</span>
