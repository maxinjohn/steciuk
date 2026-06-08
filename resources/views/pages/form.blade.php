@extends('layouts.app')

@section('title', $page->seo_title ?? $page->title . ' | ' . $siteName)
@section('description', $page->seo_description ?? strip_tags($page->content))

@section('content')
    <x-hero
        :title="$page->hero_title ?? $page->title"
        :subtitle="$page->hero_subtitle"
        :image="$page->featured_image"
        size="small"
    />

    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            @if ($page->content)
                <div class="prose-church mb-10 text-center">{!! safeHtml($page->content) !!}</div>
            @endif

            <x-card>
                @php
                    $formComponent = match ($page->slug) {
                        'prayer-request' => 'forms.prayer-request-form',
                        'new-member' => 'forms.new-member-form',
                        'contact' => 'forms.contact-form',
                        default => 'forms.contact-form',
                    };
                @endphp

                @livewire($formComponent)
            </x-card>
        </div>
    </section>

    @if ($page->contentBlocks->isNotEmpty())
        <x-content-blocks :blocks="$page->contentBlocks" />
    @endif
@endsection
