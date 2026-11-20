<x-layouts.mobile title="Masuk">
    <div class="flex flex-col gap-sm pt-2xl">
        <p class="font-headline text-display font-bold text-primary">Kampus Quest</p>
        <p class="text-body text-on-surface-variant">Laporkan kerusakan fasilitas, pantau perbaikannya, kumpulkan XP.</p>
    </div>

    <form method="POST" action="{{ route('login.attempt') }}" class="card flex flex-col gap-lg p-lg" data-submitting="Memeriksa...">
        @csrf
        <h1 class="section-title">Masuk</h1>
        <div class="flex flex-col gap-xs">
            <label for="identifier" class="field-label">NIM atau NIP</label>
            <input id="identifier" name="identifier" type="text" inputmode="text" autocomplete="username" value="{{ old('identifier') }}" required autofocus
                   @class(['input font-code', 'input-error' => $errors->has('identifier')])>
            @error('identifier')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col gap-xs">
            <label for="password" class="field-label">Kata sandi</label>
            <div class="relative">
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       @class(['input pr-[52px]', 'input-error' => $errors->has('password')]) @error('password') aria-describedby="password-error" @enderror>
                <button type="button" class="btn-icon absolute right-[2px] top-1/2 -translate-y-1/2" data-toggle-password="password" aria-label="Tampilkan kata sandi" aria-pressed="false">
                    <span data-icon-show><x-icon name="visibility" class="h-5 w-5" /></span>
                    <span data-icon-hide hidden><x-icon name="visibility_off" class="h-5 w-5" /></span>
                </button>
            </div>
            @error('password')<p id="password-error" class="field-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <label class="flex min-h-[44px] items-center gap-sm font-label text-label">
            <input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded-sm border-outline text-primary focus:ring-primary">
            Tetap masuk di perangkat ini
        </label>
        <button type="submit" class="btn-primary w-full">Masuk</button>
        <p class="meta">Lupa kata sandi? Hubungi admin Sarpras kampus.</p>
    </form>

    <div class="flex flex-col gap-sm border-t border-outline-variant pt-lg">
        <a href="{{ route('verify') }}" class="link">Pengurus BEM: cek keaslian sertifikat tanpa login</a>
    </div>
</x-layouts.mobile>
