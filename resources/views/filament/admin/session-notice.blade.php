@if (request()->boolean('expired') || session('status'))
    <div
        class="admin-session-notice mb-4 rounded-2xl border border-amber-300/70 bg-amber-50/95 px-4 py-3 text-sm text-amber-950 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
        role="status"
    >
        <p class="font-semibold">Session expired</p>
        <p class="mt-1 leading-relaxed opacity-90">
            {{ session('status') ?: 'Your admin session ended for security. Sign in again to continue parish work.' }}
        </p>
    </div>
@endif
