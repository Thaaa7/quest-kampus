@props([
    'title',
    'heading' => null,
    'subtitle' => null,
    'back' => null,
    'backLabel' => 'Kembali',
    'nav' => null,
    'codeHeading' => false,
    'dark' => false,
    'wordmark' => false,
])
@php
    $hasSticky = isset($sticky) && trim($sticky) !== '';
    $padding = match (true) {
        $nav && $hasSticky => 'pb-[168px]',
        (bool) $nav => 'pb-[96px]',
        $hasSticky => 'pb-[104px]',
        default => 'pb-2xl',
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body @class(['bg-camera-surface' => $dark])>
<a href="#konten" class="skip-link">Lewati ke konten</a>
<div @class(['mx-auto flex min-h-screen w-full max-w-[480px] flex-col', 'bg-background' => ! $dark, 'bg-camera-surface text-on-camera-surface' => $dark])>
    @if ($heading || $back || $wordmark)
        <header @class(['sticky top-0 z-20 flex min-h-[64px] items-center gap-sm px-sm py-sm', 'border-b border-outline-variant bg-surface' => ! $dark, 'bg-camera-surface' => $dark])>
            @if ($back)
                <a href="{{ $back }}" @class(['btn-icon', 'text-on-camera-surface hover:bg-white/10' => $dark]) aria-label="{{ $backLabel }}">
                    <x-icon name="{{ $dark ? 'close' : 'arrow_back' }}" />
                </a>
            @else
                <span class="w-sm"></span>
            @endif
            <div class="flex min-w-0 flex-1 flex-col">
                @if ($wordmark)
                    <span class="font-headline text-title font-bold text-primary">Kampus Quest</span>
                @endif
                @if ($heading)
                    <h1 @class(['truncate', 'font-code text-code font-semibold' => $codeHeading, 'font-title text-title' => ! $codeHeading])>{{ $heading }}</h1>
                @endif
                @if ($subtitle)
                    <p @class(['truncate font-caption text-caption', 'text-on-surface-variant' => ! $dark, 'text-white/80' => $dark])>{{ $subtitle }}</p>
                @endif
            </div>
            {{ $actions ?? '' }}
        </header>
    @endif

    <main id="konten" @class(['flex flex-1 flex-col gap-xl px-lg py-xl', $padding])>
        <x-flash />
        {{ $slot }}
    </main>

    @if ($hasSticky || $nav)
        <div class="fixed inset-x-0 bottom-0 z-30 mx-auto w-full max-w-[480px]">
            @if ($hasSticky)
                <div class="border-t border-outline-variant bg-background px-lg py-md">
                    {{ $sticky }}
                </div>
            @endif
            @if ($nav)
                <x-bottom-nav :active="$nav" />
            @endif
        </div>
    @endif
</div>
{{ $after ?? '' }}
</body>
</html>
