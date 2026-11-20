@props(['active' => null])
@php
    $items = [
        'pindai' => ['Pindai', 'qr_code_scanner', route('scan')],
        'quest' => ['Quest Saya', 'assignment', route('my-quests')],
        'peringkat' => ['Peringkat', 'leaderboard', route('leaderboard')],
        'profil' => ['Profil', 'person', route('profile')],
    ];
@endphp
<nav class="grid h-[64px] grid-cols-4 border-t border-outline-variant bg-surface" aria-label="Navigasi utama">
    @foreach ($items as $key => [$label, $icon, $url])
        <a href="{{ $url }}" @if ($active === $key) aria-current="page" @endif
           @class(['flex flex-col items-center justify-center gap-xs font-caption text-caption', 'font-semibold text-primary' => $active === $key, 'text-on-surface-variant hover:text-on-surface' => $active !== $key])>
            <x-icon :name="$icon" :fill="$active === $key" class="h-6 w-6" />
            <span>{{ $label }}</span>
        </a>
    @endforeach
</nav>
