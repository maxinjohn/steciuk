@extends('layouts.app')

@section('title', $page->seo_title ?? $page->title . ' | ' . $siteName)
@section('description', $page->seo_description ?? strip_tags($page->content))

@section('content')
    <x-page-band
        :title="$page->hero_title ?? $page->title"
        :subtitle="$page->hero_subtitle"
        kicker="Get in touch"
    />

    <section class="page-section py-10 sm:py-14">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            @if ($page->content)
                <div class="prose-church mb-8 text-center">{!! safeHtml($page->content) !!}</div>
            @endif

            <div class="form-gen-z card-modern">
                @php
                    $formComponent = match ($page->slug) {
                        'prayer-request' => 'forms.prayer-request-form',
                        'new-member' => 'forms.new-member-form',
                        'contact' => 'forms.contact-form',
                        default => 'forms.contact-form',
                    };
                @endphp

                @livewire($formComponent)
            </div>
        </div>
    </section>

    @if ($page->contentBlocks->isNotEmpty())
        <x-content-blocks :blocks="$page->contentBlocks" />
    @endif
@endsection
