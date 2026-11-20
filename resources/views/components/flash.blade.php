@if (session('status'))
    <div class="notice-success flex items-start gap-sm" role="status">
        <x-icon name="check_circle" class="mt-[2px] h-5 w-5" />
        <p>{{ session('status') }}</p>
    </div>
@endif
@if (session('error'))
    <div class="notice-error flex items-start gap-sm" role="alert">
        <x-icon name="error" class="mt-[2px] h-5 w-5" />
        <p>{{ session('error') }}</p>
    </div>
@endif
