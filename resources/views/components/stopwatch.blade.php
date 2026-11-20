@props(['since', 'until' => null])
@php($seconds = (int) $since->diffInSeconds($until ?? now(), true))
<span {{ $attributes->class('font-code tabular-nums') }} @if (! $until) data-stopwatch-since="{{ $since->timestamp }}" @endif>{{ \App\Support\Format::clock($seconds) }}</span>
