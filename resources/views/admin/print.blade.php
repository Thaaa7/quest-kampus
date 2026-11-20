<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => 'Cetak Stiker QR'])
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        @media print { .sheet { gap: 6mm !important; } .cut { outline: 1px dashed #8A8F86; outline-offset: 2mm; } }
    </style>
</head>
<body class="bg-background print:bg-white">
<main class="mx-auto flex max-w-[210mm] flex-col gap-xl p-lg print:p-0">
    <header class="no-print flex flex-wrap items-center justify-between gap-md">
        <div class="flex flex-col gap-xs">
            <h1 class="font-headline text-headline">Stiker QR, dicetak {{ \App\Support\Format::date(now()) }}</h1>
            <p class="meta">{{ $rooms->count() }} stiker. Ukuran tiap stiker 8 x 8 cm, potong mengikuti garis putus-putus.</p>
        </div>
        <div class="flex gap-sm">
            <a href="{{ $back }}" class="btn-secondary btn-sm">Kembali</a>
            <button type="button" class="btn-primary btn-sm" data-print><x-icon name="print" class="h-5 w-5" /> Cetak</button>
        </div>
    </header>
    <div class="sheet grid grid-cols-1 gap-lg sm:grid-cols-2">
        @foreach ($rooms as $room)
            <div class="cut break-inside-avoid">
                @include('admin.partials.sticker', ['room' => $room, 'qr' => $qrs[$room->id]])
            </div>
        @endforeach
    </div>
</main>
</body>
</html>
