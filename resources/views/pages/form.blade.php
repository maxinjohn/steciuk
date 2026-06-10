@extends('layouts.app')

@section('title', $page->seo_title ?? $page->title . ' | ' . $siteName)
@section('description', $page->seo_description ?? strip_tags($page->content))

@section('content')
    @php
        [$scripture, $scriptureRef] = match ($page->slug) {
            'prayer-request' => [
                'The prayer of a righteous person is powerful and effective.',
                'James 5:16',
            ],
            'new-member' => [
                'For it is by grace you have been saved, through faith — and this is not from yourselves, it is the gift of God.',
                'Ephesians 2:8',
            ],
            default => [
                'The Lord is near to all who call on him, to all who call on him in truth.',
                'Psalm 145:18',
            ],
        };
    @endphp

    <x-page-intro
        :title="$page->hero_title ?? $page->title"
        :subtitle="$page->hero_subtitle"
        kicker="Evangelical Oriental Protestant · Connect"
        :scripture="$scripture"
        :scripture-ref="$scriptureRef"
        :show-strips="true"
    />

    <section class="page-section page-section--article py-10 sm:py-12 md:py-14">
        <div class="page-section-inner mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            @if ($page->content)
                <div class="prose-church prose-church--page mb-8 text-center">{!! safeHtml($page->content) !!}</div>
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
