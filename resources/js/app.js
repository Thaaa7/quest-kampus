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

/* Pemindai QR ruangan. Hanya alamat stiker Kampus Quest yang diterima. */
function setupScanner() {
    const root = $('[data-scanner]');
    if (!root) return;
    const prefix = root.dataset.prefix;
    const video = $('video', root);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    const denied = $('#scan-denied');
    const live = $('#scan-live');
    const unknown = $('#scan-unknown');
    const torchBtn = $('[data-torch]', root);
    let stream = null;
    let busy = false;
    let lastWrong = '';

    const detector = 'BarcodeDetector' in window ? new window.BarcodeDetector({ formats: ['qr_code'] }) : null;

    const handle = (text) => {
        if (!text) return;
        if (text.startsWith(prefix)) {
            busy = true;
            window.location.href = text;
            return;
        }
        if (text !== lastWrong) {
            lastWrong = text;
            unknown.showModal();
        }
    };

    const scan = async () => {
        if (!stream || busy) return;
        if (!unknown.open && video.readyState >= 2) {
            try {
                if (detector) {
                    const codes = await detector.detect(video);
                    handle(codes[0]?.rawValue);
                } else {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    ctx.drawImage(video, 0, 0);
                    const data = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    handle(jsQR(data.data, data.width, data.height, { inversionAttempts: 'dontInvert' })?.data);
                }
            } catch {
                /* frame belum siap, coba di frame berikutnya */
            }
        }
        setTimeout(() => requestAnimationFrame(scan), 200);
    };

    const start = async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            live.hidden = true;
            denied.hidden = false;
            $('[data-denied-reason]', denied).textContent = 'Browser ini belum bisa membuka kamera dari halaman ini. Buka aplikasi kamera bawaan ponsel lalu arahkan ke stiker QR, tautannya akan membuka Kampus Quest.';
            return;
        }
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
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
