import jsQR from 'jsqr';

const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

/* Stopwatch durasi kerusakan: angka dari server diperbarui tiap detik. */
function pad(n) {
    return String(n).padStart(2, '0');
}

function formatClock(seconds) {
    const s = Math.max(0, Math.floor(seconds));
    return `${pad(Math.floor(s / 3600))}:${pad(Math.floor((s % 3600) / 60))}:${pad(s % 60)}`;
}

function startStopwatches() {
    const items = $$('[data-stopwatch-since]');
    if (!items.length) return;
    const tick = () => {
        const now = Date.now();
        items.forEach((el) => {
            el.textContent = formatClock((now - Number(el.dataset.stopwatchSince) * 1000) / 1000);
        });
    };
    tick();
    setInterval(tick, 1000);
}

/* Dialog: tombol data-dialog-open="id" membuka <dialog>, Escape menutup (bawaan browser). */
function setupDialogs() {
    $$('[data-dialog-open]').forEach((btn) => {
        btn.addEventListener('click', () => $(`#${btn.dataset.dialogOpen}`)?.showModal());
    });
    $$('[data-dialog-close]').forEach((btn) => {
        btn.addEventListener('click', () => btn.closest('dialog')?.close());
    });
    $$('dialog[data-open-on-load]').forEach((d) => d.showModal());
}

/* Tombol kirim berubah jadi state memuat supaya tidak terkirim dua kali. */
function setupSubmitting() {
    $$('form[data-submitting]').forEach((form) => {
        form.addEventListener('submit', () => {
            const btn = $('button[type="submit"]', form) || (form.id && $(`button[type="submit"][form="${form.id}"]`));
            if (!btn) return;
            btn.disabled = true;
            btn.dataset.label = btn.textContent;
            btn.textContent = form.dataset.submitting;
        });
    });
}

function setupCounters() {
    $$('[data-counter]').forEach((field) => {
        const out = $(`#${field.dataset.counter}`);
        const update = () => {
            out.textContent = `${field.value.length}/${field.maxLength}`;
        };
        field.addEventListener('input', update);
        update();
    });
}

function setupPasswordToggle() {
    $$('[data-toggle-password]').forEach((btn) => {
        const input = $(`#${btn.dataset.togglePassword}`);
        btn.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', String(show));
            btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            $('[data-icon-show]', btn).hidden = show;
            $('[data-icon-hide]', btn).hidden = !show;
        });
    });
}

function setupCopy() {
    $$('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(btn.dataset.copy);
                const label = $('[data-copy-label]', btn);
                const original = label.textContent;
                label.textContent = 'Tersalin';
                setTimeout(() => (label.textContent = original), 2000);
            } catch {
                window.prompt('Salin nomor seri ini:', btn.dataset.copy);
            }
        });
    });
}

/* Tombol cetak stiker aktif setelah minimal satu ruangan dicentang. */
function setupRequireChecked() {
    $$('form[data-require-checked]').forEach((form) => {
        const btn = $('button[type="submit"]', form);
        const update = () => (btn.disabled = !$$('input[type="checkbox"]:checked', form).length);
        form.addEventListener('change', update);
        update();
    });
}

function setupPrint() {
    $$('[data-print]').forEach((btn) => btn.addEventListener('click', () => window.print()));
}

/* Peringatan saat kategori yang dipilih sudah punya laporan aktif di ruangan ini. */
function setupDuplicateHint() {
    const form = $('[data-report-form]');
    if (!form) return;
    const box = $('#duplicate-hint');
    const link = $('#duplicate-link');
    const label = $('#duplicate-label');
    $$('input[name="category"]', form).forEach((radio) => {
        radio.addEventListener('change', () => {
            const url = radio.dataset.joinUrl;
            box.hidden = !url;
            if (url) {
                link.href = url;
                label.textContent = radio.dataset.short;
            }
        });
    });
    $('input[name="category"]:checked', form)?.dispatchEvent(new Event('change'));
}

/*
 * Kamera untuk foto laporan. Foto diambil dari kamera langsung (getUserMedia).
 * Bila browser tidak mengizinkan (misalnya situs dibuka lewat http di jaringan
 * lokal), dipakai input file dengan atribut capture yang membuka kamera ponsel.
 */
function setupCameraCapture() {
    $$('[data-camera]').forEach((root) => {
        const input = $('input[type="file"]', root);
        const preview = $('[data-camera-preview]', root);
        const img = $('img', preview);
        const empty = $('[data-camera-empty]', root);
        const dialog = $('#camera-dialog');
        const video = $('video', dialog);
        const status = $('[data-camera-status]', dialog);
        let stream = null;

        const showFile = (file) => {
            img.src = URL.createObjectURL(file);
            preview.hidden = false;
            empty.hidden = true;
            $('[data-camera-error]', root)?.remove();
            $$('[data-enable-on-photo]', root.closest('form') || document).forEach((btn) => (btn.disabled = false));
        };

        const stop = () => {
            stream?.getTracks().forEach((t) => t.stop());
            stream = null;
        };

        const open = async () => {
            if (!navigator.mediaDevices?.getUserMedia) {
                input.click();
                return;
            }
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            } catch {
                input.click();
                return;
            }
            video.srcObject = stream;
            status.textContent = '';
            dialog.showModal();
        };

        $$('[data-camera-open]', root).forEach((btn) => btn.addEventListener('click', open));
        input.addEventListener('change', () => input.files[0] && showFile(input.files[0]));

        $('[data-camera-shoot]', dialog).onclick = () => {
            if (!video.videoWidth) {
                status.textContent = 'Kamera belum siap. Tunggu sebentar lalu coba lagi.';
                return;
            }
            const canvas = document.createElement('canvas');
            const scale = Math.min(1, 1600 / Math.max(video.videoWidth, video.videoHeight));
            canvas.width = Math.round(video.videoWidth * scale);
            canvas.height = Math.round(video.videoHeight * scale);
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.toBlob((blob) => {
                const file = new File([blob], `foto-${Date.now()}.jpg`, { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
                showFile(file);
                dialog.close();
            }, 'image/jpeg', 0.85);
        };
        dialog.addEventListener('close', stop);
    });
}

/* Baca QR dari bagian tengah gambar (crop 1 = seluruh gambar), diperkecil supaya cepat. */
function decodeQr(source, width, height, ctx, { crop = 1, maxSide = 720, inversion = 'dontInvert' } = {}) {
    const sw = width * crop;
    const sh = height * crop;
    const scale = Math.min(1, maxSide / Math.max(sw, sh));
    const w = Math.max(1, Math.round(sw * scale));
    const h = Math.max(1, Math.round(sh * scale));
    ctx.canvas.width = w;
    ctx.canvas.height = h;
    ctx.drawImage(source, (width - sw) / 2, (height - sh) / 2, sw, sh, 0, 0, w, h);
    const data = ctx.getImageData(0, 0, w, h);
    return jsQR(data.data, w, h, { inversionAttempts: inversion })?.data ?? null;
}

/* BarcodeDetector hanya dipakai kalau browser benar-benar mendukung QR. */
async function createQrDetector() {
    if (!('BarcodeDetector' in window)) return null;
    try {
        const formats = await window.BarcodeDetector.getSupportedFormats?.();
        if (formats && !formats.includes('qr_code')) return null;
        return new window.BarcodeDetector({ formats: ['qr_code'] });
    } catch {
        return null;
    }
}

/* Pemindai QR ruangan. Hanya alamat stiker Kampus Quest yang diterima.
   jsQR selalu ikut membaca, karena di sebagian ponsel Android detektor bawaan
   browser tidak mengembalikan hasil apa pun tanpa pesan galat. */
function setupScanner() {
    const root = $('[data-scanner]');
    if (!root) return;
    const prefix = root.dataset.prefix;
    const video = $('video', root);
    const ctx = document.createElement('canvas').getContext('2d', { willReadFrequently: true });
    const denied = $('#scan-denied');
    const live = $('#scan-live');
    const unknown = $('#scan-unknown');
    const torchBtn = $('[data-torch]', root);
    const photoInput = $('[data-scan-photo]', root);
    const photoStatus = $('[data-scan-photo-status]', root);
    let stream = null;
    let detector = null;
    let busy = false;
    let lastWrong = '';
    let frame = 0;

    const handle = (raw) => {
        const text = raw?.trim();
        if (!text) return false;
        if (text.startsWith(prefix)) {
            busy = true;
            window.location.href = text;
            return true;
        }
        if (text !== lastWrong) {
            lastWrong = text;
            unknown.showModal();
        }
        return true;
    };

    const scan = async () => {
        if (!stream || busy) return;
        if (!unknown.open && video.readyState >= 2 && video.videoWidth) {
            let text = null;
            if (detector) {
                try {
                    text = (await detector.detect(video))[0]?.rawValue ?? null;
                } catch {
                    detector = null;
                }
            }
            if (!text) {
                try {
                    // Bergantian: area bingkai di tengah, lalu seluruh tampilan kamera.
                    text = decodeQr(video, video.videoWidth, video.videoHeight, ctx, { crop: frame % 2 ? 1 : 0.65 });
                } catch {
                    /* frame belum siap, coba di frame berikutnya */
                }
            }
            frame += 1;
            handle(text);
        }
        setTimeout(() => requestAnimationFrame(scan), 150);
    };

    photoInput?.addEventListener('change', async () => {
        const file = photoInput.files?.[0];
        photoInput.value = '';
        if (!file) return;
        photoStatus.hidden = false;
        photoStatus.textContent = 'Membaca QR dari foto...';
        try {
            const image = await createImageBitmap(file);
            let text = null;
            if (detector) {
                text = (await detector.detect(image).catch(() => []))[0]?.rawValue ?? null;
            }
            for (const crop of [1, 0.6]) {
                if (text) break;
                text = decodeQr(image, image.width, image.height, ctx, { crop, maxSide: 1200, inversion: 'attemptBoth' });
            }
            image.close?.();
            if (handle(text)) {
                photoStatus.hidden = true;
            } else {
                photoStatus.textContent = 'QR belum terbaca. Foto lebih dekat sampai QR terlihat jelas dan tidak buram, lalu coba lagi.';
            }
        } catch {
            photoStatus.textContent = 'Foto tidak bisa dibuka. Coba ambil foto lagi.';
        }
    });

    const start = async () => {
        detector = await createQrDetector();
        if (!navigator.mediaDevices?.getUserMedia) {
            live.hidden = true;
            denied.hidden = false;
            $('[data-denied-reason]', denied).textContent = 'Browser ini belum bisa membuka kamera dari halaman ini. Pakai tombol Foto QR di bawah, atau buka aplikasi kamera bawaan ponsel lalu arahkan ke stiker QR.';
            return;
        }
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false,
            });
        } catch {
            live.hidden = true;
            denied.hidden = false;
            return;
        }
        video.srcObject = stream;
        await video.play().catch(() => {});
        const track = stream.getVideoTracks()[0];
        if (track?.getCapabilities?.().torch) {
            torchBtn.hidden = false;
            let on = false;
            torchBtn.addEventListener('click', async () => {
                on = !on;
                await track.applyConstraints({ advanced: [{ torch: on }] }).catch(() => {});
                torchBtn.setAttribute('aria-pressed', String(on));
            });
        }
        scan();
    };

    $('[data-scan-retry]', unknown)?.addEventListener('click', () => {
        lastWrong = '';
        unknown.close();
    });
    window.addEventListener('pagehide', () => stream?.getTracks().forEach((t) => t.stop()));
    start();
}

document.addEventListener('DOMContentLoaded', () => {
    startStopwatches();
    setupDialogs();
    setupSubmitting();
    setupCounters();
    setupPasswordToggle();
    setupCopy();
    setupPrint();
    setupRequireChecked();
    setupDuplicateHint();
    setupCameraCapture();
    setupScanner();
});
