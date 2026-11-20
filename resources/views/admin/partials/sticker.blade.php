{{-- Stiker 8 x 8 cm. Dipakai di pratinjau admin dan halaman cetak. --}}
<div class="sticker mx-auto flex aspect-square w-full max-w-[8cm] flex-col items-center justify-between border border-outline-variant bg-white p-[0.5cm] text-center text-on-surface">
    <span class="font-headline text-[13px] font-bold leading-none text-primary">Kampus Quest</span>
    <div class="aspect-square w-[62%] [&>svg]:h-full [&>svg]:w-full" role="img" aria-label="QR code ruangan {{ $room->code }}">{!! $qr !!}</div>
    <div class="flex flex-col items-center gap-[2px]">
        <span class="font-code text-[15px] font-semibold leading-none">{{ $room->code }}</span>
        <span class="font-label text-[11px] leading-tight">{{ $room->name }}</span>
        <span class="font-label text-[11px] font-semibold leading-tight">Ada yang rusak? Pindai untuk melapor.</span>
    </div>
</div>
