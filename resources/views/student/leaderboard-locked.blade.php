@php use App\Support\Format; @endphp
<x-layouts.mobile title="Peringkat Fakultas" heading="Peringkat Fakultas" nav="peringkat">
    <section class="flex flex-col items-center gap-lg py-2xl text-center">
        <x-icon name="lock" class="h-12 w-12 text-on-surface-variant" />
        <h2 class="font-headline text-headline">Papan peringkat terbuka di {{ Format::xp($required) }}</h2>
        <p class="text-body text-on-surface-variant">Capai Tier 1: Penjaga Kampus untuk masuk peringkat fakultas. XP kamu sekarang {{ Format::number($user->xp) }}, kurang {{ Format::xp($required - $user->xp) }}.</p>
        <a href="{{ route('scan') }}" class="btn-primary w-full">Pindai QR Ruangan</a>
    </section>
</x-layouts.mobile>
