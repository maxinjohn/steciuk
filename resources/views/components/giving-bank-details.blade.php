@props([
    'bankDetails' => [],
    'compact' => false,
])

@if (filled($bankDetails['bank_name'] ?? null) || filled($bankDetails['account_number'] ?? null))
    <dl @class([
        'grid gap-3 text-sm text-ink',
        'sm:grid-cols-2' => ! $compact,
        'gap-2' => $compact,
    ])>
        @if (filled($bankDetails['bank_name']))
            <div><dt class="text-ink-muted">Bank</dt><dd class="font-medium">{{ $bankDetails['bank_name'] }}</dd></div>
        @endif
        @if (filled($bankDetails['account_name']))
            <div><dt class="text-ink-muted">Account name</dt><dd class="font-medium">{{ $bankDetails['account_name'] }}</dd></div>
        @endif
        @if (filled($bankDetails['sort_code']))
            <div><dt class="text-ink-muted">Sort code</dt><dd class="font-medium">{{ $bankDetails['sort_code'] }}</dd></div>
        @endif
        @if (filled($bankDetails['account_number']))
            <div><dt class="text-ink-muted">Account number</dt><dd class="font-medium">{{ $bankDetails['account_number'] }}</dd></div>
        @endif
        @if (filled($bankDetails['reference']))
            <div @class(['sm:col-span-2' => ! $compact])><dt class="text-ink-muted">Payment reference</dt><dd class="font-medium">{{ $bankDetails['reference'] }}</dd></div>
        @endif
    </dl>
@endif
