@php
    use App\Http\Middleware\ThrottleAdminLogin;

    $locked = session('admin_login_locked');
    $seconds = $locked['seconds'] ?? (ThrottleAdminLogin::isLocked(request()) ? ThrottleAdminLogin::secondsUntilUnlocked(request()) : 0);
@endphp

@if ($seconds > 0)
    <div
        class="mb-4 rounded-2xl border border-amber-300/70 bg-amber-50 px-4 py-3 text-sm text-amber-950 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
        role="alert"
    >
        <p class="font-semibold">Too many sign-in attempts</p>
        <p class="mt-1 leading-relaxed opacity-90">
            {{ \App\Http\Middleware\ThrottleAdminLogin::lockoutMessage($seconds) }}
        </p>
    </div>
@endif
