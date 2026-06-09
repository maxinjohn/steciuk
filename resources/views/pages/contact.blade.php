@extends('layouts.app')

@section('title', $page->seo_title ?? $page->title . ' | ' . $siteName)
@section('description', $page->seo_description ?? strip_tags($page->content))

@section('content')
    <x-page-intro
        :title="$page->hero_title ?? $page->title"
        :subtitle="$page->hero_subtitle ?? 'Reach our parish office for worship, pastoral care, or prayer'"
        kicker="UK Parish · Connect"
        scripture="The Lord is near to all who call on him, to all who call on him in truth."
        scripture-ref="Psalm 145:18"
    />

    <section class="page-section py-10 sm:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-5">
                <div class="lg:col-span-2 space-y-6">
                    <x-card class="contact-office-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand">We are here for you</p>
                        <h2 class="mt-2 font-bold text-2xl font-semibold text-ink">{{ $contactOfficeHeading }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-muted">{{ $contactOfficeIntro }}</p>
                        <ul class="mt-6 space-y-4 text-ink-muted" role="list">
                            @if ($siteAddress)
                                <li class="flex gap-3">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                    <span>{{ $siteAddress }}</span>
                                </li>
                            @endif
                            @if ($sitePhone)
                                <li class="flex gap-3">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                    <a href="tel:{{ preg_replace('/\s+/', '', $sitePhone) }}" class="transition hover:text-brand">{{ $sitePhone }}</a>
                                </li>
                            @endif
                            @if ($siteEmail)
                                <li class="flex gap-3">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                    <a href="mailto:{{ $siteEmail }}" class="transition hover:text-brand">{{ $siteEmail }}</a>
                                </li>
                            @endif
                            @if ($charityNumber)
                                <li class="flex gap-3 text-sm">
                                    <span class="mt-0.5 font-semibold text-brand">Charity</span>
                                    <span>{{ $charityNumber }}</span>
                                </li>
                            @endif
                        </ul>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <x-button href="{{ url('/prayer-request') }}" variant="outline" class="!text-sm">Submit a Prayer Request</x-button>
                            <x-button href="{{ url('/service-times') }}" variant="outline" class="!text-sm">Service Times</x-button>
                        </div>

                        <div class="mt-8 flex gap-3">
                            @if ($socialYoutube)
                                <a href="{{ $socialYoutube }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[var(--site-surface-2)] text-ink transition hover:bg-[var(--site-brand)] hover:text-white" aria-label="YouTube">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                </a>
                            @endif
                            @if ($socialFacebook)
                                <a href="{{ $socialFacebook }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[var(--site-surface-2)] text-ink transition hover:bg-[var(--site-brand)] hover:text-white" aria-label="Facebook">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                            @endif
                            @if ($socialInstagram)
                                <a href="{{ $socialInstagram }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[var(--site-surface-2)] text-ink transition hover:bg-[var(--site-brand)] hover:text-white" aria-label="Instagram">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                            @endif
                        </div>
                    </x-card>

                    <x-card class="border border-brand/20 bg-gradient-to-br from-[var(--site-surface)] to-[var(--site-surface-2)]">
                        <p class="text-sm leading-relaxed text-ink-muted">
                            <span class="font-semibold text-brand">Pastoral care:</span>
                            You are welcome in Christ. If you need prayer, counsel, or simply someone to listen, our clergy and parish family are glad to walk with you.
                        </p>
                    </x-card>
                </div>

                <div class="lg:col-span-3">
                    @if ($page->content)
                        <div class="prose-church mb-10">{!! safeHtml($page->content) !!}</div>
                    @endif

                    <x-card class="contact-form-card shadow-lg ring-1 ring-[var(--site-border)]">
                        <h2 class="font-bold text-2xl font-semibold text-ink">{{ $contactFormHeading }}</h2>
                        <p class="mt-2 text-ink-muted">{{ $contactFormIntro }}</p>
                        <div class="mt-6">
                            @livewire('forms.contact-form')
                        </div>
                    </x-card>
                </div>
            </div>

            @if ($googleMapsEmbed)
                <div class="mt-12 overflow-hidden rounded-2xl shadow-lg ring-1 border border-[var(--site-border)]">
                    <div class="aspect-video [&>iframe]:h-full [&>iframe]:w-full">{!! safeEmbed($googleMapsEmbed) !!}</div>
                </div>
            @endif
        </div>
    </section>

    @if ($page->contentBlocks->isNotEmpty())
        <x-content-blocks :blocks="$page->contentBlocks" />
    @endif
@endsection
