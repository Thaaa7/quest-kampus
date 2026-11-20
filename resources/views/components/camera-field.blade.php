@props(['name', 'label', 'hint' => null, 'button' => 'Ambil Foto', 'required' => true])
@php($error = $errors->first($name))
<div data-camera class="flex flex-col gap-sm">
    <span class="field-label" id="{{ $name }}-label">{{ $label }} @if ($required)<span class="meta">(wajib)</span>@endif</span>
    <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*" capture="environment" class="sr-only" aria-labelledby="{{ $name }}-label" @if ($hint) aria-describedby="{{ $name }}-hint" @endif tabindex="-1">
    <div data-camera-empty @class(['flex flex-col items-center gap-md rounded-lg border-2 border-dashed bg-surface px-lg py-xl text-center', 'border-accent' => $error, 'border-outline' => ! $error])>
        <x-icon name="photo_camera" class="h-8 w-8 text-on-surface-variant" />
        <button type="button" class="btn-secondary btn-sm" data-camera-open>{{ $button }}</button>
    </div>
    <div data-camera-preview hidden class="flex flex-col gap-sm">
        <img alt="Pratinjau foto yang baru diambil" class="aspect-[4/3] w-full rounded-lg border border-outline-variant object-cover" width="400" height="300">
        <button type="button" class="btn-ghost btn-sm self-start" data-camera-open>
            <x-icon name="refresh" class="h-5 w-5" /> Ambil Ulang
        </button>
    </div>
    @if ($hint)
        <p id="{{ $name }}-hint" class="meta">{{ $hint }}</p>
    @endif
    @if ($error)
        <p class="field-error" data-camera-error role="alert">{{ $error }}</p>
    @endif
</div>
