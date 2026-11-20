@props(['title', 'active'])
@php($flagged = \App\Models\Quest::open()->whereNotNull('flagged_at')->count())
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body>
<a href="#konten" class="skip-link">Lewati ke konten</a>
<div class="flex min-h-screen">
    <aside class="hidden w-[240px] shrink-0 flex-col justify-between border-r border-outline-variant bg-surface p-lg lg:flex">
        <div class="flex flex-col gap-xl">
            <div class="flex flex-col gap-xs">
                <a href="{{ route('admin.stats') }}" class="font-headline text-title font-bold text-primary">Kampus Quest</a>
                <span class="meta">Admin Kampus</span>
            </div>
            <x-admin-nav :active="$active" :flagged="$flagged" />
        </div>
        <div class="flex items-center justify-between gap-sm border-t border-outline-variant pt-lg">
            <div class="flex min-w-0 items-center gap-sm">
                <x-avatar :user="auth()->user()" size="sm" class="ring-0" />
                <span class="truncate font-caption text-caption">{{ auth()->user()->name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-ghost btn-sm px-sm">Keluar</button>
            </form>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex min-h-[64px] items-center justify-between border-b border-outline-variant bg-surface px-lg lg:hidden">
            <a href="{{ route('admin.stats') }}" class="font-headline text-title font-bold text-primary">Kampus Quest</a>
            <button type="button" class="btn-secondary btn-sm" data-dialog-open="admin-menu" aria-haspopup="dialog">Menu</button>
        </header>
        <dialog id="admin-menu" class="dialog m-0 ml-auto h-full max-h-none w-[280px] rounded-none p-lg" aria-label="Menu admin">
            <div class="flex h-full flex-col gap-xl">
                <div class="flex items-center justify-between">
                    <span class="meta">Admin Kampus</span>
                    <button type="button" class="btn-icon" data-dialog-close aria-label="Tutup menu"><x-icon name="close" /></button>
                </div>
                <x-admin-nav :active="$active" :flagged="$flagged" />
                <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                    @csrf
                    <button type="submit" class="btn-secondary w-full">Keluar</button>
                </form>
            </div>
        </dialog>
        <main id="konten" class="mx-auto flex w-full max-w-[1280px] flex-col gap-xl px-lg py-xl lg:px-xl">
            <x-flash />
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
