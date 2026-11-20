@props(['status', 'size' => 'sm'])
@php
    $map = [
        'active' => ['AKTIF', 'border-primary bg-surface text-primary'],
        'in_progress' => ['DIKERJAKAN', 'border-secondary bg-surface text-secondary'],
        'resolved' => ['SELESAI', 'border-primary bg-primary text-on-primary'],
        'raid' => ['RAID BOSS', 'border-accent bg-accent text-on-accent'],
        'rejected' => ['DITOLAK', 'border-outline bg-surface text-on-surface-variant'],
        'valid' => ['SERTIFIKAT SAH', 'border-primary bg-primary text-on-primary'],
        'notfound' => ['TIDAK TERDAFTAR', 'border-accent bg-accent text-on-accent'],
        'unprinted' => ['BELUM DICETAK', 'border-secondary bg-surface text-secondary'],
    ];
    [$label, $colors] = $map[$status] ?? [strtoupper($status), 'border-outline text-on-surface-variant'];
    $sizing = $size === 'lg' ? 'px-md py-sm text-label' : 'px-sm py-xs text-caption';
@endphp
<span {{ $attributes->class(['inline-flex items-center whitespace-nowrap rounded-sm border-[1.5px] font-caption font-semibold uppercase tracking-[0.04em]', $colors, $sizing]) }}>{{ $label }}</span>
