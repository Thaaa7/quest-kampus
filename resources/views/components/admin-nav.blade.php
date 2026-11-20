@props(['active', 'flagged' => 0])
@php
    $groups = [
        'Pantau' => [
            'stats' => ['Statistik', 'bar_chart', route('admin.stats'), null],
        ],
        'Kelola' => [
            'moderation' => ['Moderasi', 'flag', route('admin.moderation'), $flagged ?: null],
            'rooms' => ['Gedung & Ruangan', 'meeting_room', route('admin.rooms'), null],
        ],
    ];
@endphp
<nav class="flex flex-col gap-lg" aria-label="Menu admin">
    @foreach ($groups as $group => $items)
        <div class="flex flex-col gap-xs">
            <span class="px-sm font-caption text-caption text-on-surface-variant">{{ $group }}</span>
            @foreach ($items as $key => [$label, $icon, $url, $badge])
                <a href="{{ $url }}" @if ($active === $key) aria-current="page" @endif
                   @class(['flex min-h-[44px] items-center justify-between gap-sm rounded-md px-sm font-label text-label', 'bg-primary-container font-semibold text-primary' => $active === $key, 'text-on-surface-variant hover:bg-background hover:text-on-surface' => $active !== $key])>
                    <span class="flex items-center gap-sm"><x-icon :name="$icon" :fill="$active === $key" class="h-5 w-5" />{{ $label }}</span>
                    @if ($badge)
                        <span class="rounded-sm bg-accent-container px-sm py-xs font-code text-caption text-accent"><span class="sr-only">Menunggu ditinjau: </span>{{ $badge }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endforeach
</nav>
