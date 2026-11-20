{{-- Field ruangan untuk dialog tambah dan halaman ubah. $bag = error bag yang dipakai. --}}
@php($val = fn ($key, $default = null) => $bag->any() || $errors->getBag('default')->any() ? old($key, $default) : $default)
<div class="flex flex-col gap-xs">
    <label for="building_id" class="field-label">Gedung</label>
    <select id="building_id" name="building_id" required @class(['input', 'input-error' => $bag->has('building_id')])>
        @foreach ($buildings as $b)
            <option value="{{ $b->id }}" @selected((string) $val('building_id', $selectedBuilding) === (string) $b->id)>{{ $b->name }}</option>
        @endforeach
    </select>
    @if ($bag->has('building_id'))<p class="field-error">{{ $bag->first('building_id') }}</p>@endif
</div>
<div class="grid gap-md sm:grid-cols-[1fr_120px]">
    <div class="flex flex-col gap-xs">
        <label for="code" class="field-label">Kode ruangan</label>
        <input id="code" name="code" value="{{ $val('code', $room?->code) }}" maxlength="20" required placeholder="Contoh: C-302" @class(['input font-code uppercase', 'input-error' => $bag->has('code')])>
        @if ($bag->has('code'))<p class="field-error" role="alert">{{ $bag->first('code') }}</p>@endif
    </div>
    <div class="flex flex-col gap-xs">
        <label for="floor" class="field-label">Lantai</label>
        <input id="floor" name="floor" type="number" min="0" max="50" value="{{ $val('floor', $room?->floor ?? 1) }}" required @class(['input font-code', 'input-error' => $bag->has('floor')])>
        @if ($bag->has('floor'))<p class="field-error">{{ $bag->first('floor') }}</p>@endif
    </div>
</div>
<div class="flex flex-col gap-xs">
    <label for="name" class="field-label">Nama ruangan</label>
    <input id="name" name="name" value="{{ $val('name', $room?->name) }}" maxlength="100" required placeholder="Contoh: Lab Komputer 302" @class(['input', 'input-error' => $bag->has('name')])>
    @if ($bag->has('name'))<p class="field-error">{{ $bag->first('name') }}</p>@endif
</div>
