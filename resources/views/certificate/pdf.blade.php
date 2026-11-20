@php use App\Support\Format; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>{{ $info['name'] }} {{ $certificate->serial }}</title>
<style>
    @font-face { font-family: 'Jakarta'; font-weight: 400; src: url('{{ resource_path('fonts/PlusJakartaSans-Regular.ttf') }}') format('truetype'); }
    @font-face { font-family: 'Jakarta'; font-weight: 700; src: url('{{ resource_path('fonts/PlusJakartaSans-Bold.ttf') }}') format('truetype'); }
    @font-face { font-family: 'PlexMono'; font-weight: 400; src: url('{{ resource_path('fonts/IBMPlexMono-Medium.ttf') }}') format('truetype'); }
    @page { margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Jakarta', 'DejaVu Sans', sans-serif; color: #1B2420; font-size: 13px; }
    .frame { position: absolute; top: 32px; left: 32px; right: 32px; bottom: 32px; border: 1px solid #1E5B4B; }
    .inner { position: absolute; top: 40px; left: 48px; right: 48px; bottom: 40px; }
    .mono { font-family: 'PlexMono', 'DejaVu Sans Mono', monospace; }
    .muted { color: #4F5A54; }
    .logo { display: inline-block; width: 56px; height: 56px; border: 1px dashed #8A8F86; color: #4F5A54; font-size: 8px; text-align: center; line-height: 56px; vertical-align: middle; }
    .wordmark { display: inline-block; vertical-align: middle; margin-left: 12px; font-weight: 700; font-size: 18px; color: #1E5B4B; }
    .serial { position: absolute; top: 18px; right: 0; font-size: 12px; }
    .body { position: absolute; top: 120px; left: 0; width: 560px; }
    .label { font-size: 12px; font-weight: 700; color: #1E5B4B; letter-spacing: 0.04em; text-transform: uppercase; }
    .name { font-size: 40px; font-weight: 700; line-height: 46px; margin: 6px 0 4px; }
    .facts { margin-top: 28px; border-collapse: collapse; }
    .facts td { padding: 0 20px; border-left: 1px solid #D9D6CC; font-size: 13px; }
    .facts td:first-child { padding-left: 0; border-left: 0; }
    .facts strong { display: block; font-size: 18px; margin-top: 2px; }
    .skp { margin-top: 24px; background: #E3EEEA; color: #1E5B4B; padding: 10px 14px; font-size: 12px; width: 420px; }
    .issued { position: absolute; bottom: 0; left: 0; font-size: 12px; }
    .qr { position: absolute; bottom: 0; right: 0; width: 200px; text-align: center; font-size: 11px; }
    .qr img { width: 120px; height: 120px; }
</style>
</head>
<body>
<div class="frame"></div>
<div class="inner">
    <div>
        <span class="logo">[LOGO KAMPUS]</span><span class="wordmark">Kampus Quest</span>
        <span class="serial mono">No. seri {{ $certificate->serial }}</span>
    </div>

    <div class="body">
        <div class="label">{{ $info['name'] }}</div>
        <p class="muted" style="margin: 18px 0 0;">Diberikan kepada</p>
        <div class="name">{{ $certificate->user->name }}</div>
        <div class="muted"><span class="mono">{{ $certificate->user->identifier }}</span>, {{ $certificate->user->faculty }}</div>
        <p style="margin-top: 20px; font-size: 14px; line-height: 21px;">Atas kontribusi aktif melaporkan dan memvalidasi kerusakan fasilitas kampus melalui Kampus Quest.</p>

        <table class="facts">
            <tr>
                <td><span class="muted">Tier</span><strong>{{ $info['tier_name'] }}</strong></td>
                <td><span class="muted">Total XP</span><strong>{{ Format::number($certificate->xp_at_issue) }}</strong></td>
                <td><span class="muted">Laporan valid</span><strong>{{ $certificate->valid_reports }}</strong></td>
            </tr>
        </table>

        <div class="skp">Rekomendasi konversi: setara {{ $info['skp'] }} SKP, divalidasi BEM melalui QR di sertifikat ini.</div>
    </div>

    <div class="issued">Diterbitkan {{ Format::longDate($certificate->issued_at) }}</div>
    <div class="qr">
        <img src="{{ $qr }}" alt="QR verifikasi">
        <div style="margin-top: 4px; font-weight: 700;">Pindai untuk verifikasi</div>
        <div class="mono muted" style="font-size: 9px; margin-top: 2px;">{{ $certificate->verifyUrl() }}</div>
    </div>
</div>
</body>
</html>
