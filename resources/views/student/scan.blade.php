<x-layouts.mobile title="Pindai QR Ruangan" heading="Pindai QR Ruangan" nav="pindai" dark>
    <x-slot:actions>
        <button type="button" class="btn-icon text-on-camera-surface hover:bg-white/10" data-torch hidden aria-label="Nyalakan senter" aria-pressed="false">
            <x-icon name="flashlight_on" />
        </button>
    </x-slot:actions>

    <div data-scanner data-prefix="{{ $roomUrlPrefix }}" class="flex flex-1 flex-col gap-xl">
        <section id="scan-live" class="flex flex-col items-center gap-xl">
            <div class="relative w-full overflow-hidden rounded-lg">
                <video class="aspect-[3/4] w-full bg-black object-cover" autoplay playsinline muted aria-label="Tampilan kamera"></video>
                <div class="scan-frame pointer-events-none absolute left-1/2 top-1/2 h-[240px] w-[240px] -translate-x-1/2 -translate-y-1/2" aria-hidden="true"></div>
            </div>
            <p class="text-center font-label text-label">Arahkan kamera ke stiker QR di pintu atau dinding ruangan.</p>
            <div class="flex w-full items-start gap-sm rounded-lg bg-surface p-md text-on-surface">
                <x-icon name="info" class="h-5 w-5 text-primary" />
                <p class="font-label text-label">Laporan hanya bisa dibuat dari lokasi ruangan, supaya tidak ada laporan palsu.</p>
            </div>
        </section>

        <section id="scan-denied" hidden class="flex flex-col items-center gap-lg rounded-lg bg-background px-lg py-2xl text-center text-on-surface">
            <x-icon name="no_photography" class="h-12 w-12 text-on-surface-variant" />
            <h2 class="section-title">Kamera belum diizinkan</h2>
            <p class="text-body text-on-surface-variant" data-denied-reason>Izinkan akses kamera untuk memindai QR ruangan. Buka pengaturan situs di browser, ubah izin Kamera menjadi Izinkan, lalu muat ulang halaman ini.</p>
            <a href="{{ route('scan') }}" class="btn-primary w-full">Coba Lagi</a>
        </section>
    </div>

    <x-slot:after>
        <dialog id="scan-unknown" class="dialog mb-0 w-full max-w-[480px] rounded-b-none sm:mb-auto sm:rounded-lg" aria-labelledby="scan-unknown-title">
            <div class="flex flex-col gap-md p-xl">
                <h2 id="scan-unknown-title" class="section-title">QR ini bukan stiker ruangan</h2>
                <p class="text-body text-on-surface-variant">Cari stiker bertuliskan Kampus Quest di dekat pintu ruangan.</p>
                <button type="button" class="btn-primary w-full" data-scan-retry>Pindai Ulang</button>
            </div>
        </dialog>
    </x-slot:after>
</x-layouts.mobile>
