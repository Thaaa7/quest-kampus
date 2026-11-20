<dialog id="camera-dialog" class="m-0 h-full max-h-none w-full max-w-none bg-camera-surface p-0 text-on-camera-surface sm:m-auto sm:h-auto sm:max-w-[480px] sm:rounded-lg" aria-label="Kamera">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between px-lg py-md">
            <h2 class="font-title text-title">Ambil foto</h2>
            <button type="button" class="btn-icon text-on-camera-surface hover:bg-white/10" data-dialog-close aria-label="Tutup kamera">
                <x-icon name="close" />
            </button>
        </div>
        <video class="w-full flex-1 bg-black object-cover sm:aspect-[3/4] sm:flex-none" autoplay playsinline muted></video>
        <p data-camera-status class="min-h-[24px] px-lg pt-sm text-center font-label text-label" role="status"></p>
        <div class="flex justify-center px-lg pb-xl pt-md">
            <button type="button" data-camera-shoot class="inline-flex h-[72px] w-[72px] items-center justify-center rounded-full border-4 border-white bg-white/20 hover:bg-white/30" aria-label="Jepret foto">
                <x-icon name="photo_camera" class="h-8 w-8" />
            </button>
        </div>
    </div>
</dialog>
