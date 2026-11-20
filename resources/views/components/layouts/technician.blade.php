@props(['title'])
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body>
<a href="#konten" class="skip-link">Lewati ke konten</a>
<header class="border-b border-outline-variant bg-surface">
    <div class="mx-auto flex min-h-[64px] max-w-[1440px] flex-wrap items-center gap-lg px-lg">
        <a href="{{ route('technician.queue') }}" class="font-headline text-title font-bold text-primary">Kampus Quest</a>
        <span class="meta hidden sm:inline">Teknisi Sarpras</span>
        <div class="ml-auto flex items-center gap-sm">
            <x-avatar :user="auth()->user()" size="sm" class="ring-0" />
            <span class="hidden font-label text-label md:inline">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-ghost btn-sm">
                    <x-icon name="logout" class="h-5 w-5" /> Keluar
                </button>
            </form>
        </div>
    </div>
</header>
<main id="konten" class="mx-auto flex max-w-[1440px] flex-col gap-xl px-lg py-xl">
    <x-flash />
    {{ $slot }}
</main>
</body>
</html>
