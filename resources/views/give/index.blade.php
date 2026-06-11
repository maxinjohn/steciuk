@extends('layouts.app')

@section('title', $heading . ' | ' . $siteName)
@section('description', \Illuminate\Support\Str::limit(strip_tags($intro), 160))

@section('content')
    <x-page-intro
        :title="$heading"
        :subtitle="$intro"
        kicker="UK Parish · Giving"
        scripture="Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver."
        scripture-ref="2 Corinthians 9:7"
    />

    <section class="page-section py-10 sm:py-14">
        <div class="page-section-inner mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="member-portal-card">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand">Give as a member</p>
                    <h2 class="member-portal-panel-title mt-2">Track your giving</h2>
                    <p class="member-portal-panel-intro">{{ $memberIntro }}</p>

                    @if ($canReportGiving && $memberSummary)
                        <div class="member-giving-stats mt-5">
                            <div class="member-giving-stat">
                                <p class="member-giving-stat-label">Your approved giving</p>
                                <p class="member-giving-stat-value">£{{ number_format($memberSummary['personal'], 2) }}</p>
                            </div>
                            @if (($memberSummary['pending_count'] ?? 0) > 0)
                                <div class="member-giving-stat member-giving-stat--pending">
                                    <p class="member-giving-stat-label">Awaiting verification</p>
                                    <p class="member-giving-stat-value">{{ $memberSummary['pending_count'] }} {{ str('entry')->plural($memberSummary['pending_count']) }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <ol class="mt-5 space-y-2 text-sm text-ink-muted">
                        @if ($hasBankDetails)
                            <li><span class="font-semibold text-ink">1.</span> Transfer your gift using the parish bank details below.</li>
                        @else
                            <li><span class="font-semibold text-ink">1.</span> Make your gift by bank transfer or another method shown below.</li>
                        @endif
                        <li><span class="font-semibold text-ink">2.</span> Sign in and report the gift so parish admin can verify it.</li>
                        <li><span class="font-semibold text-ink">3.</span> Approved gifts appear in your account and PDF statements.</li>
                    </ol>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        @auth
                            @if ($canReportGiving)
                                <a href="{{ route('account') }}#giving" class="btn btn-primary">Report a gift & view history</a>
                            @else
                                <a href="{{ route('registration.pending') }}" class="btn btn-primary">Account pending approval</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">Sign in to report giving</a>
                            <a href="{{ route('register') }}" class="btn btn-secondary">Create member account</a>
                        @endauth
                    </div>
                </div>

                <div class="member-portal-card">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand">Give anonymously</p>
                    <h2 class="member-portal-panel-title mt-2">No registration needed</h2>
                    <p class="member-portal-panel-intro">{{ $anonymousIntro }}</p>

                    <ol class="mt-5 space-y-2 text-sm text-ink-muted">
                        @if (filled($bankDetails['payment_link']))
                            <li><span class="font-semibold text-ink">1.</span> Pay online using the link below, or use bank transfer if you prefer.</li>
                        @elseif ($hasBankDetails)
                            <li><span class="font-semibold text-ink">1.</span> Transfer your gift using the parish bank details below.</li>
                        @else
                            <li><span class="font-semibold text-ink">1.</span> Contact the parish office for bank transfer details.</li>
                        @endif
                        <li><span class="font-semibold text-ink">2.</span> No sign-in or personal details are stored for anonymous gifts.</li>
                        <li><span class="font-semibold text-ink">3.</span> Thank you — your gift supports worship and mission.</li>
                    </ol>

                    @if (filled($bankDetails['payment_link']))
                        <a href="{{ $bankDetails['payment_link'] }}" class="btn btn-secondary mt-6 inline-flex" target="_blank" rel="noopener noreferrer">Pay online</a>
                    @endif
                </div>
            </div>

            @if ($hasBankDetails)
                <div class="member-portal-card mt-6">
                    <h2 class="member-portal-panel-title">Parish bank transfer details</h2>
                    <p class="member-portal-panel-intro">Use these details for member or anonymous giving. Members can report the gift afterwards from their account.</p>
                    <div class="mt-5 rounded-2xl border border-[var(--site-border)] bg-[var(--site-surface)] p-5">
                        <x-giving-bank-details :bank-details="$bankDetails" />
                    </div>
                </div>
            @elseif (filled($siteEmail) || filled($sitePhone))
                <div class="member-portal-card mt-6">
                    <h2 class="member-portal-panel-title">Bank transfer</h2>
                    <p class="member-portal-panel-intro">
                        For parish bank account details, please contact the parish office
                        @if (filled($siteEmail))
                            at <a href="mailto:{{ $siteEmail }}" class="text-brand hover:underline">{{ $siteEmail }}</a>
                        @endif
                        @if (filled($siteEmail) && filled($sitePhone))
                            or
                        @endif
                        @if (filled($sitePhone))
                            call <a href="tel:{{ preg_replace('/\s+/', '', $sitePhone) }}" class="text-brand hover:underline">{{ $sitePhone }}</a>
                        @endif
                        .
                    </p>
                </div>
            @endif

            <div class="member-portal-card mt-6">
                <h2 class="member-portal-panel-title">Ways to give</h2>
                <p class="member-portal-panel-intro">Members can report any of these methods from their account after giving. Anonymous gifts via bank transfer need no follow-up.</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($paymentMethods as $value => $label)
                        <div class="rounded-xl border border-[var(--site-border)] bg-[var(--site-surface)] px-4 py-3 text-sm">
                            <p class="font-semibold text-ink">{{ $label }}</p>
                            @if ($value === 'bank_transfer' && $hasBankDetails)
                                <p class="mt-1 text-ink-muted">Use the parish account above. Reference: {{ $bankDetails['reference'] ?: 'your surname + Giving' }}.</p>
                            @elseif ($value === 'bank_transfer')
                                <p class="mt-1 text-ink-muted">Contact the parish office for account details.</p>
                            @elseif ($value === 'card_online' && filled($bankDetails['payment_link']))
                                <p class="mt-1 text-ink-muted"><a href="{{ $bankDetails['payment_link'] }}" class="text-brand hover:underline" target="_blank" rel="noopener noreferrer">Open online payment page</a></p>
                            @else
                                <p class="mt-1 text-ink-muted">Give in person or by post, then report from your member account if you would like it recorded.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
